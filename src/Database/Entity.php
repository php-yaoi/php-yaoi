<?php

namespace Yaoi\Database;

use Yaoi\Database;
use Yaoi\Database\Definition\Table;
use Yaoi\Entity\Exception;
use Yaoi\Mappable;
use Yaoi\Sql\SelectInterface;
use Yaoi\Sql\Symbol;
use Yaoi\BaseClass;
use Yaoi\Database\Definition\Column;
use Yaoi\String\Utils;

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
    public static function table()
    {
        $className = get_called_class();
        $table = &self::$tables[$className];
        if (null !== $table) {
            return $table;
        }
        $columns = new \stdClass();
        static::setUpColumns($columns);
        $schemaName = Utils::fromCamelCase(str_replace('\\', '', $className));
        $table = new Table($columns, self::getDatabase($className), $schemaName);
        $table->className = $className;
        static::setUpTable($table, $columns);

        return $table;
    }

    /**
     * @return \stdClass|static
     */
    public static function columns() {
        return static::table()->columns;
    }


    /**
     * @return string
     * @deprecated use ::table()->schemaName
     * @todo remove method
     */
    protected static function getTableName()
    {
        return static::table()->schemaName;
    }

    protected $persistent;

    /**
     * @param null $id
     * @return null|SelectInterface|static
     * @throws Exception
     * @todo testdoc
     */
    public static function find($id = null)
    {
        $className = get_called_class();
        $table = static::table();
        $statement = $table->database()->select($table);
        $statement->bindResultClass($className);

        if ($id instanceof static) {
            foreach ($id->toArray(true) as $name => $value) {
                $statement->where("? = ?", new Symbol($table->schemaName, $name), $value);
            }
        } elseif ($id) {
            $args = func_get_args();
            $i = 0;
            foreach ($table->primaryKey as $keyField) {
                if (!isset($args[$i])) {
                    throw new Exception('Full primary key required', Exception::KEY_MISSING);
                }
                $keyValue = $args[$i++];
                $statement->where('? = ?', new Symbol($table->schemaName, $keyField->schemaName), $keyValue);
            }
            return $statement->query()->fetchRow();
        }
        return $statement;
    }


    static function fromArray(array $row, $object = null, $source = null)
    {
        if (is_null($object)) {
            $object = new static;
        }

        foreach (static::table()->getColumns(true) as $column) {
            if (array_key_exists($column->schemaName, $row)) {
                $object->{$column->propertyName} = Column::castField($row[$column->schemaName], $column->flags);
            }
        }

        if ($source) {
            $object->persistent = true;
        }

        return $object;

    }

    public function toArray($skipNotSetProperties = false)
    {
        $result = array();
        foreach (static::table()->getColumns(true) as $column) {
            $value = $this->{$column->propertyName};
            if (null === $value) {
                if ($skipNotSetProperties) {
                    continue;
                }
            } else {
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


    public function update()
    {
        $table = static::table();
        $update = $table->database()->update($table->schemaName);
        $data = $this->toArray();

        foreach ($table->primaryKey as $keyField) {
            if (!isset($data[$keyField->schemaName])) {
                throw new Exception('Primary key required for update', Exception::KEY_MISSING);
            }
            $update->where("? = ?", new Symbol($keyField->schemaName), $data[$keyField->schemaName]);
            unset($data[$keyField->schemaName]);
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
        $data = $this->toArray();

        if ($autoId = $table->autoIdColumn) {
            if (empty($data[$autoId->schemaName])) {
                unset($data[$autoId->schemaName]);
            }
        }

        $insert->valuesRow($data);

        $query = $insert->query();

        if ($autoId) {
            if (empty($this->{$autoId->propertyName})) {
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
    public static function bindDatabase(Contract $database) {
        $class = get_called_class();
        if (!isset(self::$databases[$class]) || self::$databases[$class] !== $database) {
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


    public function findOrSave() {
        $item = self::find($this)->query()->fetchRow();
        if (!$item) {
            $this->save();
        }
        else {
            self::fromArray($item->toArray(), $this);
        }
        return $this;
    }


}