<?php

/**
 * Class Database_Mock_DriverPlay
 * TODO proxy quote
 * TODO proxy quoteSymbol
 */
class Database_Mock_DriverPlay extends Database_Driver implements Mock_Able {
    /**
     * @var Mock
     */
    private $lastQuery;

    public function query($statement)
    {
        $queryMock = $this->mock->branch(Database_Driver_MockProxy::QUERY, $statement);
        $this->lastQuery = $queryMock;
        return $queryMock;
    }

    public function lastInsertId()
    {
        $queryMock = $this->lastQuery;
        return $queryMock->get(Database_Driver_MockProxy::LAST_INSERT_ID);
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     * @return integer
     */
    public function rowsAffected($queryMock)
    {
        return $queryMock->get(Database_Driver_MockProxy::ROWS_AFFECTED);
    }


    public function escape($value)
    {
        return $this->mock->branch(Database_Driver_MockProxy::ESCAPE)->get($value);
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     */
    public function rewind($queryMock)
    {
        return $queryMock->branch(Database_Driver_MockProxy::REWIND)->get();
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     */
    public function fetchAssoc($queryMock)
    {
        return $queryMock->branch(Database_Driver_MockProxy::ASSOC_ROWS)->get();
    }


    /**
     * @var Mock_DataSetPlay $mock
     */
    protected $mock;
    public function mock(Mock $dataSet = null)
    {
        if ($dataSet->mode === Mock::MODE_PLAY) {
            $this->mock = $dataSet;
        }
        else {
            throw new Mock_Exception(Mock_Exception::PLAY_REQUIRED, 'Play data set required');
        }
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     */
    public function queryErrorMessage($queryMock)
    {
        return $queryMock->get(Database_Driver_MockProxy::ERROR_MESSAGE);
    }


    public function disconnect() {

    }

} 