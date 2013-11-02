<?php


/**
 * Class Database_Driver_Mysqli
 *
 * connect
 * disconnect
 * query -> res
 * affectedRows
 * insertId
 * beginTransaction
 * commitTransaction
 *
 * fetchAll -> res
 *
 *
 *
 */

abstract class Database_Abstract_Driver_Mysqli extends Database_Driver  {
    /**
     * @var mysqli
     */
    private $mysqli;

    protected function connect() {
        $host = $this->dsn->hostname;
        if ($this->dsn->persistent) {
            if (is_null($host)) {
                $host = ini_get("mysqli.default_host");
            }
            $host = 'p:' . $host;
        }

        $this->mysqli = new mysqli(
            $host,
            $this->dsn->username,
            $this->dsn->password,
            $this->dsn->path,
            $this->dsn->port,
            $this->dsn->unixSocket
        );

        if ($this->mysqli->connect_error) {
            throw new Database_Exception('Connection error: (' . $this->mysqli->connect_errno . ') '
            . $this->mysqli->connect_error, Database_Exception::CONNECTION_ERROR);
        }

        return $this;
    }


    /**
     * @param mysqli_result $result
     * @return mixed
     */
    public function rewind($result)
    {
        return $result->data_seek(0);
    }

    /**
     * @param mysqli_result $result
     * @return mixed
     */
    public function fetchAssoc($result) {
        return $result->fetch_assoc();
    }

    public function disconnect() {
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }


    public function lastInsertId($result) {
        return $this->mysqli->insert_id;
    }

    public function escape($string) {
        return $this->mysqli->real_escape_string($string);
    }


    /**
     * @param $statement
     * @return bool|mysqli_result
     */
    public function query($statement) {
        if (null === $this->mysqli) {
            $this->connect();
        }
        return $this->mysqli->query($statement);
    }

    public function __destruct() {
        $this->disconnect();
    }


}