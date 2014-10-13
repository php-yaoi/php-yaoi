<?php

class Entity_SimpleMysql extends Base_Class {
    /**
     * @var string
     */
    static public $tableName;
    static public $idField = 'id';

    private $fetched = false;
    private $originalData;

    private static $columns = array();

    private static $databases = array();
    public static function bindDatabase(Database_Interface $db) {
        self::$databases[get_called_class()] = $db;
    }


    public static function getTableName() {
        if (null === static::$tableName) {
            return get_called_class();
        }
        else {
            return static::$tableName;
        }
    }


    public static function getIdName() {
        return static::$idField;
    }

    /**
     * @return Database_Interface
     */
    private static function db() {
        $class = get_called_class();
        if (isset(self::$databases[$class])) {
            return self::$databases[$class];
        }
        else {
            return Yaoi::db($class);
        }
    }

    public static function getColumns() {
        $tableName = self::getTableName();
        if (!isset(self::$columns[$tableName])) {
            self::$columns[$tableName] = self::db()
                ->query("DESC `$tableName`")
                ->fetchPairs(0, 0);
        }
        return self::$columns[$tableName];
    }


    /**
     * @param $id
     * @return $this
     */
    public static function getById($id) {
        $tableName = self::getTableName();
        $idField = self::$idField;
        $row = self::db()->query("SELECT * FROM `$tableName` WHERE `$idField` = ?", $id)->fetchRow();
        if ($row) {
            $obj = new static();
            $obj->fetched = true;
            foreach ($row as $key => $value) {
                $obj->$key = $value;
            }

            return $obj;
        }
        else {
            return null;
        }
    }


    public function update() {
        $update = self::db()->update(self::getTableName());
        $data = array();
        foreach (static::getColumns() as $column) {
            if (property_exists($this, $column)) {
                $data[$column] = $this->$column;
            }
        }
        $idField = static::$idField;
        $update->where("`$idField` = ?", $data[$idField]);
        $update->set($data);
        unset($data[$idField]);
        $update->query();
        $this->fetched = true;
        return $this;
    }

    public function insert() {
        $insert = self::db()->insert(self::getTableName());
        $data = array();
        foreach (static::getColumns() as $column) {
            if (property_exists($this, $column)) {
                $data[$column] = $this->$column;
            }
        }
        $insert->valuesRow($data);
        if (!isset($data[static::$idField])) {
            $id = $insert->query()->lastInsertId();
            $this->{static::$idField} = $id;
        }

        return $this;
    }

    public function save() {
        if (!isset($this->{self::$idField})) {
            $this->insert();
        }
        else {
            $this->update();
        }
    }

    public static function fromArray($data) {
        $instance = new static();
        foreach ($data as $name => $value) {
            $instance->$name = $value;
        }
        return $instance;
    }

}