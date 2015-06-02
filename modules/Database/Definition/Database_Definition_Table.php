<?php

class Database_Definition_Table extends Base_Class {
    public $autoIncrement;
    public $primaryKey = array();
    public $columns = array();
    public $defaults = array();
    public $notNull = array();


    public static function castField($value, $columnType) {
        if (is_object($value)) {
            $value = (string)$value;
        }

        if ($columnType !== Database::COLUMN_TYPE_AUTO) {
            switch ($columnType) {
                case Database::COLUMN_TYPE_FLOAT:
                    $value = (float)$value;
                    break;
                case Database::COLUMN_TYPE_INTEGER:
                    $value = (int)$value;
                    break;
                case Database::COLUMN_TYPE_STRING:
                    $value = (string)$value;
            }
        }

        return $value;
    }

}