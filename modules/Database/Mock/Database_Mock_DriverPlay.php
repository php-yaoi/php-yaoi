<?php

class Database_Mock_DriverPlay extends Database_Driver implements Mock_Able {
    /**
     * @var Mock_DataSetPlay
     */
    private $lastQuery;

    public function query($statement)
    {
        $queryMock = $this->mock->branch(self::QUERY, $statement);
        $this->lastQuery = $queryMock;
        return $queryMock;
    }

    public function lastInsertId()
    {
        $queryMock = $this->lastQuery;
        return $queryMock->get(self::LAST_INSERT_ID);
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     * @return integer
     */
    public function rowsAffected($queryMock)
    {
        return $queryMock->get(self::ROWS_AFFECTED);
    }


    public function escape($value)
    {
        return $this->mock->branch(self::ESCAPE)->get($value);
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     */
    public function rewind($queryMock)
    {
        return $queryMock->branch(self::REWIND)->get();
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     */
    public function fetchAssoc($queryMock)
    {
        return $queryMock->branch(self::ASSOC_ROWS)->get();
    }


    /**
     * @var Mock_DataSetPlay $mock
     */
    protected $mock;
    public function mock(Mock_DataSet $dataSet = null)
    {
        if ($dataSet instanceof Mock_DataSetPlay) {
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
        return $queryMock->get(self::ERROR_MESSAGE);
    }


    public function disconnect() {

    }

} 