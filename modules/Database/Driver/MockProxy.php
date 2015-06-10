<?php

namespace Yaoi\Database\Driver;
use Yaoi\Database\Driver;
use Yaoi\Database\Utility\Contract;
use Mock;
use Mock_DataSetCapture;

class MockProxy extends Driver
{
    const RESULT = 'result';
    const LAST_INSERT_ID = 'lid';
    const QUERY = 'query';
    const ESCAPE = 'escape';
    const ERROR_MESSAGE = 'error';
    const ASSOC_ROWS = 'assoc_rows';
    const REWIND = 'rewind';
    const ROWS_AFFECTED = 'rows_affected';
    /**
     * @var Driver
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
        $queryMock = $this->mock->branch(MockProxy::QUERY, $statement);
        if ($queryMock->isEmptyBranch) {
            $queryMock->temp(MockProxy::RESULT, $this->driver->query($statement));
            $this->lastQuery = $queryMock;
            $this->lastInsertId();
            $this->rowsAffected($queryMock);
        } else {
            $this->lastQuery = $queryMock;
        }
        return $queryMock;
    }

    /**
     * @return mixed
     */
    public function lastInsertId()
    {
        $queryMock = $this->lastQuery;
        $driver = $this->driver;
        return $queryMock->get(MockProxy::LAST_INSERT_ID, function () use ($queryMock, $driver) {
            $res = $driver->lastInsertId($queryMock->temp(MockProxy::RESULT));
            return $res;
        });
    }


    /**
     * @param Mock $queryMock
     * @return mixed
     */
    public function rowsAffected($queryMock)
    {
        $driver = $this->driver;
        return $queryMock->get(MockProxy::ROWS_AFFECTED, function () use ($queryMock, $driver) {
            return $driver->rowsAffected($queryMock->temp(MockProxy::RESULT));
        });
    }


    public function escape($value)
    {
        $driver = $this->driver;
        return $this->mock->branch(MockProxy::ESCAPE)->get($value, function () use ($value, $driver) {
            return $driver->escape($value);
        });
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     * @return mixed
     */
    public function rewind($queryMock)
    {
        $driver = $this->driver;
        return $queryMock->branch(MockProxy::REWIND)->get(null, function () use ($queryMock, $driver) {
            return $driver->rewind($queryMock->temp(MockProxy::RESULT));
        });
    }

    /**
     * @param Mock $queryMock
     * @return mixed
     */
    public function fetchAssoc($queryMock)
    {
        $driver = $this->driver;
        return $queryMock->branch(MockProxy::ASSOC_ROWS)->get(null, function () use ($queryMock, $driver) {
            return $driver->fetchAssoc($queryMock->temp(MockProxy::RESULT));
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
        $driver = $this->driver;
        return $queryMock->get(MockProxy::ERROR_MESSAGE, function () use ($queryMock, $driver) {
            return $driver->queryErrorMessage($queryMock->temp(MockProxy::RESULT));
        });
    }

    public function disconnect()
    {
    }

    public function getDialect()
    {
        $driver = $this->driver;
        return $this->mock->get('language', function () use ($driver) {
            return $driver->getDialect();
        });
    }

    /**
     * @return Contract
     */
    public function getUtility()
    {
        $driver = $this->driver;
        return $this->mock->get('utility', function () use ($driver) {
            return $driver->getUtility();
        });
    }


}