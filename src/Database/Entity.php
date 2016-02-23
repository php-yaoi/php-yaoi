<?php

namespace Yaoi\Database;

use Yaoi\Database;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Definition\Index;
use Yaoi\Mappable;
use Yaoi\Sql\SelectInterface;
use Yaoi\Sql\SimpleExpression;
use Yaoi\Sql\Statement;
use Yaoi\Sql\Symbol;
use Yaoi\BaseClass;
use Yaoi\Database\Definition\Column;
use Yaoi\String\Utils;
use Yaoi\Undefined;

abstract class Entity extends BaseClass implements Mappable\Contract, Entity\Contract
{
    /**
     * @var Table[]
     */
    private static $tables = array();

    /**
     * Method should return table definition of entity
     * @return Table
     */
    public static function table($alias = null)
    {
        $className = get_called_class();
        $table = &self::$tables[$className . ($alias ? ':' . $alias : '')];
        if (null !== $table) {
            return $table;
        }
        $columns = new \stdClass();
        static::setUpColumns($columns);
        $schemaName = Utils::fromCamelCase(str_replace('\\', '', $className));
        $table = new Table($columns, self::getDatabase($className), $schemaName);
        $table->entityClassName = $className;
        $table->alias = $alias;
        static::setUpTable($table, $columns);

        return $table;
    }

    /**
     * @param Table|null $table
     * @return static
     */
    public static function columns(Table $table = null) {
        return null === $table
            ? static::table()->columns
            : $table->columns;
    }


    protected $persistent;


    /**
     * @param null|static $filter
     * @return SelectInterface
     */
    public static function statement($filter = null) {
        $className = get_called_class();
        $table = static::table();
        $statement = $table->database()->select($table);
        $statement->bindResultClass($className);

        if ($filter instanceof static) {
            foreach ($filter->toArray(true) as $name => $value) {
                $statement->where("? = ?", new Symbol($table->schemaName, $name), $value);
            }
        }
        return $statement;
    }

    /**
     * @param ...$id
     * @return static
     * @throws \Yaoi\Entity\Exception
     * @todo testdoc
     */
    public static function findByPrimaryKey($id)
    {
        $className = get_called_class();
        $table = static::table();
        $statement = $table->database()->select($table);
        $statement->bindResultClass($className);

        $args = func_get_args();
        $i = 0;
        foreach ($table->primaryKey as $keyField) {
            if (!isset($args[$i])) {
                throw new \Yaoi\Entity\Exception('Full primary key required', \Yaoi\Entity\Exception::KEY_MISSING);
            }
            $keyValue = $args[$i++];
            $statement->where('? = ?', new Symbol($table->schemaName, $keyField->schemaName), $keyValue);
        }
        return $statement->query()->fetchRow();
    }

    /**
     * @return static
     */
    public function findSaved() {
        $table = $this->table();
        $statement = self::statement();
        $data = $this->toArray(true);

        $uniqueIndex = false;


        foreach ($table->indexes as $index) {
            if ($index->type !== Index::TYPE_UNIQUE && $index->type !== Index::TYPE_PRIMARY) {
                continue;
            }

            $uniqueIndex = new SimpleExpression();

            foreach ($index->columns as $column) {
                if ($column->flags & Column::AUTO_ID) {
                    $uniqueIndex = false;
                    break;
                }

                $schemaName = $column->schemaName;
                // skip unique index if null column found
                if (!isset($data[$schemaName])) {
                    $uniqueIndex = false;
                    break;
                }
                $uniqueIndex->andExpr(
                    '? = ?',
                    new Symbol($schemaName),
                    $data[$schemaName]
                );
            }

            if ($uniqueIndex) {
                break;
            }
        }

        if ($uniqueIndex) {
            $statement->where($uniqueIndex);
        }
        else {
            foreach ($table->getColumns(true) as $column) {
                $schemaName = $column->schemaName;

                if (!isset($data[$schemaName])) {
                    continue;
                }

                $statement->where(
                    '? = ?',
                    new Symbol($schemaName),
                    $data[$schemaName]
                );
            }
        }


        /** @var static $item */
        $item = $statement->query()->fetchRow();
        return $item;
    }


    private $originalData;

    static function fromArray(array $row, $object = null, $source = null)
    {
        if (is_null($object)) {
            $object = new static;
        }

        $object->originalData = array();
        foreach (static::table()->getColumns(true) as $column) {
            if (array_key_exists($column->schemaName, $row)) {
                $value = Column::castField($row[$column->schemaName], $column->flags);
                $object->{$column->propertyName} = $value;
                $object->originalData [$column->propertyName] = $value;
            }
        }

        if ($source) {
            $object->persistent = true;
        }

        return $object;
    }

    public function __construct() {
        foreach (static::table()->getColumns(true) as $column) {
            if (null === $this->{$column->propertyName}) {
                $this->{$column->propertyName} = Undefined::get();
            }
        }
    }


    public function toArray($skipNotSetProperties = false, $skipCast = false)
    {
        $result = array();
        foreach (static::table()->getColumns(true) as $column) {
            $value = $this->{$column->propertyName};
            if ($value instanceof Undefined) {
                if ($skipNotSetProperties) {
                    continue;
                }
                else {
                    $value = $column->getDefault();
                }
            } elseif (!$skipCast) {
                $value = Column::castField($value, $column->flags);
            }

            $result[$column->schemaName] = $value;
        }
        return $result;
    }

    public function save()
    {
        if ($this->persistent) {
            $this->update();
        } else {
            $this->insert();
        }
    }


    public function delete() {
        $table = static::table();
        $delete = $table->database()->delete($table->schemaName);
        $data = $this->toArray();

        foreach ($table->primaryKey as $keyField) {
            if (!isset($this->{$keyField->schemaName})) {
                throw new \Yaoi\Entity\Exception('Primary key required for delete', \Yaoi\Entity\Exception::KEY_MISSING);
            }
            $delete->where("? = ?", new Symbol($keyField->schemaName), $data[$keyField->schemaName]);
        }

        $delete->query();
        $this->persistent = false;
        return $this;
    }


    public function update()
    {
        $table = static::table();
        $update = $table->database()->update($table->schemaName);
        $data = $this->toArray(true);

        foreach ($table->primaryKey as $keyField) {
            if (!isset($data[$keyField->schemaName])) {
                throw new \Yaoi\Entity\Exception('Primary key required for update', \Yaoi\Entity\Exception::KEY_MISSING);
            }
            $update->where("? = ?", new Symbol($keyField->schemaName), $data[$keyField->schemaName]);
            unset($data[$keyField->schemaName]);
        }

        foreach ($data as $key => $value) {
            if ($this->originalData[$key] === $value) {
                unset($data[$key]);
            }
        }

        $update->set($data);
        $update->query();
        $this->persistent = true;
        return $this;
    }

    public function insert()
    {
        $table = static::table();
        $insert = $table->database()->insert($table->schemaName);
        $data = $this->toArray(true);

        $autoId = $table->autoIdColumn;

        $insert->valuesRow($data);

        $query = $insert->query();

        if ($autoId) {
            if ($this->{$autoId->propertyName} instanceof Undefined) {
                $this->{$autoId->propertyName} = $query->lastInsertId();
            }
        }

        $this->persistent = true;
        return $this;
    }

    /**
     * @var Contract[]
     */
    private static $databases = array();
    public static function bindDatabase(Contract $database, $forceTableCacheClean = false) {
        $class = get_called_class();
        if ($forceTableCacheClean || !isset(self::$databases[$class]) || self::$databases[$class] !== $database) {
            self::$databases[$class] = $database;
            if (isset(self::$tables[$class])) {
                unset(self::$tables[$class]);
            }
        }
    }

    private static function getDatabase($class) {
        if (isset(self::$databases[$class])) {
            return self::$databases[$class];
        }
        else {
            return Database::getInstance();
        }
    }


    public function findOrSave($updateRecord = false) {
        $item = $this->findSaved();

        if (!$item) {
            $this->save();
        }
        elseif (!$updateRecord) {
            self::fromArray($item->toArray(), $this);
        }
        else {

        }
        return $this;
    }


}