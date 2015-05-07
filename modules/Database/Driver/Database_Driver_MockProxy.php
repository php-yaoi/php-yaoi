<?php

class Database_Driver_MockProxy extends Database_Driver {
    /**
     * @var Database_Driver
     */
    public $driver;

    /**
     * @var Mock_DataSetCapture
     */
    private $lastQuery;

    /**
     * @param $statement
     * @return static
     */
    public function query($statement)
    {
        $queryMock = $this->mock->branch(self::QUERY, $statement);
        if ($queryMock->isEmptyBranch) {
            $queryMock->temp(self::RESULT, $this->driver->query($statement));
        }
        $this->lastQuery = $queryMock;
        return $queryMock;
    }

    /**
     * @return mixed
     */
    public function lastInsertId()
    {
        $queryMock = $this->lastQuery;
        return $queryMock->get(self::LAST_INSERT_ID, function () use ($queryMock) {
            $res = $this->driver->lastInsertId($queryMock->temp(self::RESULT));
            return $res;
        });
    }


    /**
     * @param Mock_DataSetCapture $queryMock
     * @return mixed
     */
    public function rowsAffected($queryMock)
    {
        return $queryMock->get(self::ROWS_AFFECTED, function() use ($queryMock) {
            return $this->driver->rowsAffected($queryMock->temp(self::RESULT));
        });
    }


    public function escape($value)
    {
        return $this->mock->branch(self::ESCAPE)->get($value, function () use ($value) {
            return $this->driver->escape($value);
        });
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     * @return mixed
     */
    public function rewind($queryMock)
    {
        return $queryMock->branch(self::REWIND)->get(null, function () use ($queryMock) {
            return $this->driver->rewind($queryMock->temp(self::RESULT));
        });
    }

    /**
     * @param Mock $queryMock
     * @return mixed
     */
    public function fetchAssoc($queryMock)
    {
        return $queryMock->branch(self::ASSOC_ROWS)->get(null, function () use ($queryMock) {
            return $this->driver->fetchAssoc($queryMock->temp(self::RESULT));
        });
    }


    /**
     * @var Mock
     */
    protected $mock;
    public function mock(Mock $dataSet = null)
    {
        if (null === $dataSet) {
            $dataSet = Mock::getNull();
        }

        $this->mock = $dataSet;
    }

    /**
     * @param Mock $queryMock
     * @return mixed
     */
    public function queryErrorMessage($queryMock)
    {
        return $queryMock->get(self::ERROR_MESSAGE, function () use ($queryMock) {
            return $this->driver->queryErrorMessage($queryMock->temp(self::RESULT));
        });
    }

    public function disconnect()
    {
    }
}