<?php

abstract class Entity_Database extends Base_Class implements Mappable {
    static public $primaryKey = 'id';
    static public $uniqueKeys = array();
    static public $tableName;

    protected $fetched;

    static private $databases = array();
    public static function bindDatabase(Database $db = null) {
        self::$databases[get_called_class()] = $db;
    }

    /**
     * @return Database
     * @throws Client_Exception
     */
    private static function db() {
        $class = get_called_class();
        if (isset(self::$databases[$class])) {
            return self::$databases[$class];
        }
        else {
            return Database::getInstance();
        }
    }

    /**
     *
     */
    public static function find($id = null) {
        $statement = static::db()->select(self::$tableName);
        $statement->build();
        if ($id) {
            $statement->where('? = ?', new Sql_Symbol(static::$tableName, static::$primaryKey), $id);
        }
    }



    public function pivot() {

    }

    public static function get($id) {

    }



    static function fromArray(array $row, $object = null)
    {
        if (is_null($object)) {
            $object = new static;
        }

        $object->fromProperties = array();

        foreach (static::$mappedProperties as $property) {
            $object->$property = $row[$property];
        }

        return $object;

    }

    public function toArray()
    {
        // TODO: Implement toArray() method.
    }


}