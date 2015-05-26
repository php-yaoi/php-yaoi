<?php

abstract class Database_Utility extends Base_Class implements Database_Utility_Interface {
    /**
     * @var Database_Interface
     */
    protected $db;
    public function setDatabase(Database_Interface $db) {
        $this->db = $db;
    }

}