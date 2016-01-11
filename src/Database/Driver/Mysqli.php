<?php
namespace Yaoi\Database\Driver;

use Yaoi\Database\Driver;
use Yaoi\Sql\Symbol;
use Yaoi\Database;

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
class Mysqli extends Driver implements Driver\Contract
{
    /**
     * @var \mysqli
     */
    private $mysqli;

    protected function connect()
    {
        $host = $this->dsn->hostname;
        if ($this->dsn->persistent) {
            if (is_null($host)) {
                $host = ini_get("mysqli.default_host");
            }
            $host = 'p:' . $host;
        }

        $this->mysqli = new \mysqli(
            $host,
            $this->dsn->username,
            $this->dsn->password,
            $this->dsn->path,
            $this->dsn->port,
            $this->dsn->unixSocket
        );

        if ($this->mysqli->connect_error) {
            throw new Database\Exception('Connection error: (' . $this->mysqli->connect_errno . ') '
                . $this->mysqli->connect_error, Database\Exception::CONNECTION_ERROR);
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
     * @param \mysqli_result $result
     * @return mixed
     */
    public function rewind($result)
    {
        return $result->num_rows > 0 ? $result->data_seek(0) : false;
    }

    /**
     * @param \mysqli_result $result
     * @return array|null
     */
    public function fetchAssoc($result)
    {
        return $result->fetch_assoc();
    }

    public function disconnect()
    {
        if ($this->mysqli) {
            $result = $this->mysqli->close();
            if (!$result) {
                throw new Database\Exception('Disconnect failed', Database\Exception::DISCONNECT_ERROR);
            } else {
                $this->mysqli = null;
            }
        }
    }


    public function lastInsertId()
    {
        return $this->mysqli->insert_id;
    }

    /**
     * @param \mysqli_result $result
     * @return int
     */
    public function rowsAffected($result)
    {
        if ((true === $result) || !$affected = $result->num_rows) {
            $affected = $this->mysqli->affected_rows;
        }
        return $affected;
    }

    public function escape($string)
    {
        if (null === $this->mysqli) {
            $this->connect();
        }
        return $this->mysqli->real_escape_string((string)$string);
    }


    /**
     * @param $statement
     * @return bool|\mysqli_result
     */
    public function query($statement)
    {
        if (null === $this->mysqli) {
            $this->connect();
        }
        return $this->mysqli->query($statement);
    }

    public function __destruct()
    {
        $this->disconnect();
    }


    public function queryErrorMessage($result)
    {
        return $this->mysqli->errno . ' ' . $this->mysqli->error;
    }

    public function quoteSymbol(Symbol $symbol)
    {
        $result = '';
        foreach ($symbol->names as $name) {
            $result .= '`' . str_replace('`', '``', $name) . '`.';
        }
        if ($result) {
            $result = substr($result, 0, -1);
        }
        return $result;
    }

    public function getDialect()
    {
        return Database::DIALECT_MYSQL;
    }

    /**
     * @return \Yaoi\Database\Mysql\Utility
     */
    public function getUtility()
    {
        return new Database\Mysql\Utility();
    }


}