<?php

class Database_Utility_Mysql extends Base_Class {
    private $driver;

    public function __construct(Database_Server_Mysql $driver) {
        $this->driver = $driver;
    }

    public function killSleepers($timeout = 30) {
        foreach ($this->driver->query("SHOW PROCESSLIST") as $row) {
            if ($row['Time'] > 30) {
                $this->driver->query("KILL $row[Id]");
            }
        }
        return $this;
    }

}