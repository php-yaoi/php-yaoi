<?php

abstract class Entity_Database extends Base_Class implements Mappable {
    static public $tableName;


    private static $definitions;
    public static function definition() {
        $className = get_called_class();
        $definition = &self::$definitions[$className];
        if (null === $definition) {
            $definition = new Entity_Database_Definition();
            $definition->className = $className;
            $definition->tableName = static::$tableName;
        }
        return $definition;
    }

    protected static function getTableName($className) {
        return $tableName = null === static::$tableName ? $className : static::$tableName;
    }

    protected $persistent;

    /**
     * @param null $id
     * @return null|Sql_SelectInterface|static
     * @throws Entity_Exception
     */
    public static function find($id = null) {
        $definition = static::definition();
        $tableDefinition = $definition->getTableDefinition();
        $tableName = $definition->getTableName();
        $statement = $definition->db()->select($tableName);
        $statement->bindResultClass($definition->className);

        if ($id instanceof static) {
            foreach ($id->toArray(true) as $name => $value) {
                $statement->where("? = ?", new Sql_Symbol($tableName, $name), $value);
            }
        }
        elseif ($id) {
            $args = func_get_args();
            $i = 0;
            foreach ($tableDefinition->primaryKey as $keyField) {
                if (!isset($args[$i])) {
                    throw new Entity_Exception('Full primary key required', Entity_Exception::KEY_MISSING);
                }
                $keyValue = $args[$i++];
                $statement->where('? = ?', new Sql_Symbol($tableName, $keyField), $keyValue);
            }
            return $statement->query()->fetchRow();
        }
        return $statement;
    }



    public function pivot() {

    }



    static function fromArray(array $row, $object = null, $source = null)
    {
        $definition = static::definition();
        $tableDefinition = $definition->getTableDefinition();

        if (is_null($object)) {
            $object = new static;
        }

        foreach ($tableDefinition->columns as $column => $columnType) {
            if (array_key_exists($column, $row)) {
                $object->$column = Database_Definition_Table::castField($row[$column], $columnType);
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
        foreach (static::definition()->getColumns() as $name => $type) {
            $value = $this->$name;
            if (null === $value) {
                if ($skipNotSetProperties) {
                    continue;
                }
            }
            else {
                switch ($type) {
                    case Database::COLUMN_TYPE_STRING:
                        $value = (string)$value;
                        break;
                    case Database::COLUMN_TYPE_INTEGER:
                        $value = (int)$value;
                        break;
                    case Database::COLUMN_TYPE_FLOAT:
                        $value = (float)$value;
                        break;

                    /* TODO something about timestamps
                    case Database::COLUMN_TYPE_TIMESTAMP:
                        $value = ?
                        break;
                    */
                }
            }

            $result[$name] = $value;
        }
        return $result;
    }

    public function save() {
        if ($this->persistent) {
            $this->update();
        }
        else {
            $this->insert();
        }
    }


    public function update() {
        $def = static::definition();
        $tableDefinition = $def->getTableDefinition();
        $update = $def->db()->update($def->getTableName());
        $data = array();
        foreach ($tableDefinition->columns as $column => $columnType) {
            if (property_exists($this, $column)) {
                $data[$column] = Database_Definition_Table::castField($this->$column, $columnType);
            }
        }
        foreach ($tableDefinition->primaryKey as $keyField) {
            if (!isset($data[$keyField])) {
                throw new Entity_Exception('Primary key required for update', Entity_Exception::KEY_MISSING);
            }
            $update->where("? = ?", new Sql_Symbol($keyField), $this->$keyField);
            unset($data[$keyField]);
        }
        $update->set($data);
        $update->query();
        $this->persistent = true;
        return $this;
    }

    public function insert() {
        $definition = static::definition();
        $tableDefinition = $definition->getTableDefinition();
        $insert = $definition->db()->insert($definition->getTableName());
        $data = array();
        foreach ($tableDefinition->columns as $column => $columnType) {
            if (property_exists($this, $column)) {
                $data[$column] = Database_Definition_Table::castField($this->$column, $columnType);
            }
        }
        $insert->valuesRow($data);

        $query = $insert->query();

        if ($autoId = $tableDefinition->autoIncrement) {
            if (empty($this->$autoId)) {
                $this->$autoId = $query->lastInsertId();
            }
        }

        $this->persistent = true;
        return $this;
    }




}