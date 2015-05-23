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
class Database_Driver_Mysqli extends Database_Driver  implements Database_Server_Mysql  {
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

        if ($this->dsn->charset) {
            $this->query("SET NAMES " . $this->dsn->charset);
        }

        if ($this->dsn->timezone) {
            $this->query("SET time_zone = '" . $this->dsn->timezone . "'");
        }

        return $this;
    }


    /**
     * @param mysqli_result $result
     * @return mixed
     */
    public function rewind($result)
    {
        return $result->num_rows > 0 ? $result->data_seek(0) : false;
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
            $result = $this->mysqli->close();
            if (!$result) {
                throw new Database_Exception('Disconnect failed', Database_Exception::DISCONNECT_ERROR);
            }
            else {
                $this->mysqli = null;
            }
        }
    }


    public function lastInsertId() {
        return $this->mysqli->insert_id;
    }

    /**
     * @param mysqli_result $result
     * @return int
     */
    public function rowsAffected($result) {
        if ((true === $result) || !$affected = $result->num_rows) {
            $affected = $this->mysqli->affected_rows;
        }
        return $affected;
    }

    public function escape($string) {
        if (null === $this->mysqli) {
            $this->connect();
        }
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


    public function queryErrorMessage($result) {
        return $this->mysqli->errno . ' ' . $this->mysqli->error;
    }

    public function quoteSymbol(Sql_Symbol $symbol) {
        $result = '';
        foreach ($symbol->names as $name) {
            $result .= '`' . $name . '`.';
        }
        if ($result) {
            $result = substr($result, 0, -1);
        }
        return $result;
    }

    public function getColumns($tableName)
    {
        $res = $this->query("DESC `$tableName`");
        $columns = array();
        while ($row = $res->fetch_assoc()) {
            $type = $row['Type'];
            $phpType = Database::COLUMN_TYPE_STRING;
            $field = $row['Field'];
            switch (true) {
                case 'bigint' === substr($type, 0, 6):
                case 'int' === substr($type, 0, 3):
                case 'mediumint' === substr($type, 0, 9):
                case 'smallint' === substr($type, 0, 8):
                case 'tinyint' === substr($type, 0, 7):
                    $phpType = Database::COLUMN_TYPE_INTEGER;
                    break;

                case 'decimal' === substr($type, 0, 7):
                case 'double' === $type:
                case 'float' === $type:
                    $phpType = Database::COLUMN_TYPE_FLOAT;
                    break;

                case 'date' === $type:
                case 'datetime' === $type:
                case 'timestamp' === $type:
                    $phpType = Database::COLUMN_TYPE_TIMESTAMP;
                    break;
            }

            $columns[$field] = $phpType;
        }
        return $columns;
    }


}