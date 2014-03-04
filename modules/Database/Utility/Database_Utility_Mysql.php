<?php

class Database_Utility_Mysql extends Base_Class {
    private $client;

    public function __construct(Database $client) {
        if ($client->getDriver() instanceof Database_Server_Mysql) {
            throw new Database_Exception('Wrong server type', Database_Exception::WRONG_SERVER_TYPE);
        }
        $this->client = $client;
    }

    public function killSleepers($timeout = 30) {
        foreach ($this->client->query("SHOW PROCESSLIST") as $row) {
            if ($row['Time'] > $timeout) {
                $this->client->query("KILL $row[Id]");
            }
        }
        return $this;
    }

}