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
abstract class Database_Abstract_Query implements Iterator {
    protected $statement;
    protected $binds;

    private $dbResourceId;

    public function __construct(&$statement, $binds = null, Database_Abstract_Client $client) {
        $this->statement = $statement;
        $this->binds = $binds;

        $this->dbResourceId = DependencyRepository::add($client->getDriver());
    }

    /**
     * @return Database_Driver
     */
    private function db() {
        return DependencyRepository::$items[$this->dbResourceId];
    }

    /**
     * @return string
     */
    public function build() {
        if ($this->binds) {
            $replace = array();
            foreach ($this->binds as $key => $value) {
                $replace [':' . $key] = $this->db()->quote($value);
            }
            return strtr($this->statement, $replace);
        }
        return $this->statement;
    }

    protected $executed = false;
    protected $result;
    public function execute() {
        if (!$this->result = $this->db()->query($this->build())) {
            throw new Database_Exception($this->db()->queryErrorMessage($this->result), Database_Exception::QUERY_ERROR);
        }
        $this->executed = true;
        return $this;
    }

    public function fetchAll() {
        $this->rewind();

        $result = array();

        while ($r = $this->db()->fetchAssoc($this->result)) {
            $result []= $r;
        }
        return $result;
    }

    public function fetchRow($field = null) {
        if (!$this->executed) {
            $this->execute();
        }
        $result = $this->db()->fetchAssoc($this->result);
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




}