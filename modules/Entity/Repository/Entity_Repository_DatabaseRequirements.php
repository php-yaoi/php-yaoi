<?php

interface Entity_Repository_DatabaseRequirements {
    /**
     * @return Database_Interface
     */
    public static function getDatabase();

    public static function getTableName();

    /**
     * @return string | string[]
     */
    public static function getPrimaryKey();

    /**
     * @return string[]
     */
    public static function getFields();

    /**
     * @return bool
     */
    public static function isAutoPrimaryKey();
}