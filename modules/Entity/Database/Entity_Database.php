<?php

abstract class Entity_Database extends Base_Class implements Mappable {
    static public $tableName;


    private static $definitions;
    public static function definition() {
        $className = get_called_class();
        $definition = &self::$definitions[$className];
        if (null === $definition) {
            $definition = new Entity_Database_Definition();
            $definition->tableName = static::$tableName;
            $definition->className = $className;
        }
        return $definition;
    }

    protected $fetched;

    static private $databases = array();
    public static function bindDatabase(Database $db = null) {
        self::$databases[get_called_class()] = $db;
    }

    /**
     * @return Database
     * @throws Client_Exception
     */
    private static function db($className) {
        if (isset(self::$databases[$className])) {
            return self::$databases[$className];
        }
        else {
            return Database::getInstance();
        }
    }

    /**
     * returns entity item if $id is provided
     * returns Sql_Statement
     */
    public static function find($id = null) {
        $className = get_called_class();
        $tableName = self::getTableName($className);
        $statement = self::db($className)->select($tableName);
        $statement->bindResultClass($className);
        if ($id instanceof static) {
            foreach ($id->toArray(true) as $name => $value) {
                $statement->where("? = ?", new Sql_Symbol($tableName, $name), $value);
            }
        }
        elseif ($id) {
            $statement->where('? = ?', new Sql_Symbol(static::$tableName, static::$primaryKey), $id);
            return $statement->query()->fetchRow();
        }
        return $statement;
    }



    public function pivot() {

    }



    static function fromArray(array $row, $object = null, $source = null)
    {
        if (is_null($object)) {
            $object = new static;
        }

        $object->fromProperties = array();

        foreach ($row as $property) {
            $object->$property = $row[$property];
        }

        if ($source) {
            $object->fetched = true;
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
        if ($this->fetched) {
            $this->update();
        }
        else {
            $this->insert();
        }
    }


    public function update() {
        $def = static::definition();
        $update = $def->db()->update($def->getTableName());
        $data = array();
        foreach ($def->getColumns() as $column) {
            if (property_exists($this, $column)) {
                $data[$column] = $this->$column;
            }
        }
        foreach ($def->getPrimaryKey() as $keyField) {

        }
        $idField = static::$idField;
        $update->where("`$idField` = ?", $this->$idField);
        $update->set($data);
        unset($data[$idField]);
        $update->query();
        $this->fetched = true;
        return $this;
    }

    public function insert() {
        $className = get_called_class();
        $insert = self::db($className)->insert(self::getTableName($className));
        $data = array();
        foreach (self::getColumns($className) as $column) {
            if (property_exists($this, $column)) {
                $data[$column] = $this->$column;
            }
        }
        $insert->valuesRow($data);
        if (!is_array(static::$idField)) {
            if (!isset($data[static::$idField])) {
                $id = $insert->query()->lastInsertId();
                $this->{static::$idField} = $id;
            }
        }
        else {
            $insert->query();
        }

        $this->fetched = true;

        return $this;
    }




}