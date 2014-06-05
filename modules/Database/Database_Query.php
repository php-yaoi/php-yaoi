<?php
/**
 * Class Database_Sql_Generic_Query
 *
 * db literal query
 * |sql statement
 *  |sql expression
 * |result
 * |db client
 */
class Database_Query implements Iterator {
    protected $statement;
    protected $binds;

    /**
     * @var Database
     */
    private $client;
    public function setDatabaseClient(Database $client = null) {
        $this->client = $client;
    }

    private $dbResourceId;

    public function __construct(&$statement, $binds = null, Database_Driver $driver) {
        $this->statement = $statement;
        $this->binds = $binds;

        $this->dbResourceId = DependencyRepository::add($driver);
    }

    /**
     * @return Database_Driver
     */
    private function db() {
        return DependencyRepository::$items[$this->dbResourceId];
    }


    /**
     * @return string
     * @throws Database_Exception
     */
    public function build() {
        if ($this->binds) {
            return $this->client->buildString($this->statement, $this->binds);
        }
        return $this->statement;
    }

    protected $executed = false;
    protected $result;
    public function execute() {
        $query = $this->build();
        $start = microtime(1);

        if (!$this->result = $this->db()->query($query)) {
            $error = $this->db()->queryErrorMessage($this->result);
            if (null !== $this->logResourceId) {
                /**
                 * @var Log $log
                 */
                $log = DependencyRepository::$items[$this->logResourceId];
                $log->push($error . ' ' . $query, Log::TYPE_ERROR);
            }
            $exception = new Database_Exception($error, Database_Exception::QUERY_ERROR);
            $exception->query = $query;
            throw $exception;
        }
        $this->executed = true;

        if (null !== $this->logResourceId) {
            /**
             * @var Log $log
             */
            $log = DependencyRepository::$items[$this->logResourceId];
            $log->push(round(microtime(1) - $start, 4) . ' s. (' . $this->rowsAffected() . ') ' . $query);
        }

        return $this;
    }

    public function fetchAll($keyField = null) {
        $this->rewind();

        $result = array();

        if ($keyField !== null) {
            while ($r = $this->db()->fetchAssoc($this->result)) {
                $result [$r[$keyField]]= $r;
            }
        }
        else {
            while ($r = $this->db()->fetchAssoc($this->result)) {
                $result []= $r;
            }
        }
        return $result;
    }

    public function fetchPairs() {
        $this->rewind();

        $result = array();

        while ($r = $this->db()->fetchAssoc($this->result)) {
            $r = array_values($r);
            $result [$r[0]]= $r[1];
        }
        return $result;
    }

    public function fetchRow($field = null) {
        if (!$this->executed) {
            $this->execute();
        }
        $result = $this->db()->fetchAssoc($this->result);
        if (null === $result) {
            return null;
        }
        return null === $field ? $result : $result[$field];
    }


    protected $current;
    protected $position;
    protected $valid;


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        if (null === $this->current) {
            $this->next();
        }
        return $this->current;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        if (is_null($this->current = $this->db()->fetchAssoc($this->result))) {
            $this->valid = false;
            $this->position = null;
        }
        else {
            $this->valid = true;
            ++$this->position;
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        if (null === $this->current) {
            return 0;
        }
        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        if (null === $this->current) {
            $this->next();
        }
        return $this->valid;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        if (!$this->executed) {
            $this->execute();
        }

        $this->db()->rewind($this->result);
        $this->position = -1;
        $this->valid = true;
        $this->current = null;
    }

    protected $skipAutoExecute = 0;
    public function skipAutoExecute($true = 1) {
        $this->skipAutoExecute = $true;
        return $this;
    }

    public function __destruct() {
        if (!$this->executed && !$this->skipAutoExecute) {
            $this->execute();
        }
        unset(DependencyRepository::$items[$this->dbResourceId]);
    }

    public function lastInsertId() {
        if (!$this->executed) {
            $this->execute();
        }

        return $this->db()->lastInsertId($this->result);
    }

    public function lastInsertIdIn(&$var) {
        $var = $this->lastInsertId();
        return $this;
    }

    public function rowsAffected() {
        if (!$this->executed) {
            $this->execute();
        }
        return $this->db()->rowsAffected($this->result);
    }

    public function rowsAffectedIn(&$var) {
        $var = $this->rowsAffected();
        return $this;
    }


    private $logResourceId;
    public function log(Log $log = null) {
        $this->logResourceId = DependencyRepository::add($log);
        return $this;
    }


}