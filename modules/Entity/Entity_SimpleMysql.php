<?php

class Entity_SimpleMysql {
    static public $tableName = null;
    static public $idField = 'id';

    private $fetched = false;
    private $originalData;

    private static $columns = array();

    private static $databases = array();
    public static function bindDatabase(Database_Interface $db) {
        self::$databases[get_called_class()] = $db;
    }


    private static function tableName() {
        if (null === self::$tableName) {
            return get_called_class();
        }
        else {
            return self::$tableName;
        }
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
        $tableName = self::tableName();
        if (!isset(self::$columns[$tableName])) {
            self::$columns[$tableName] = self::db()
                ->query("DESC `$tableName`")
                ->fetchPairs(0, 0);
        }
        return self::$columns[$tableName];
    }


    /**
     * @param $id
     * @return null|static
     */
    public static function getById($id) {
        $tableName = self::$tableName;
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
        $update = self::db()->statement();
        $update->update(self::$tableName);
        $data = array();
        foreach (static::getColumns() as $column) {
            $data[$column] = $this->$column;
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
        $insert = self::db()->statement();
        $insert->insert(self::$tableName);
        $data = array();
        foreach (static::getColumns() as $column) {
            $data[$column] = isset($this->$column) ? $this->$column : null;
        }
        $insert->set($data);
        if (!isset($data[static::$idField])) {
            echo $insert;
            //$id = $insert->query()->lastInsertId();
            //$this->id = $id;
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

}