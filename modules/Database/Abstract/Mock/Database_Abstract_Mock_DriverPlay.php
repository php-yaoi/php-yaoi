<?php

abstract class Database_Abstract_Mock_DriverPlay extends Database_Driver implements Mock_Able {
    public function query($statement)
    {
        $queryMock = $this->mock->branch($statement, self::QUERY);
        return $queryMock;
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     */
    public function lastInsertId($queryMock)
    {
        return $queryMock->get(self::LAST_INSERT_ID);
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     */
    public function rowsAffected($queryMock)
    {
        return $queryMock->get(self::ROWS_AFFECTED);
    }


    public function escape($value)
    {
        return $this->mock->get($value, self::ESCAPE);
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     */
    public function rewind($queryMock)
    {
        return $queryMock->get(null, self::REWIND);
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     */
    public function fetchAssoc($queryMock)
    {
        return $queryMock->get(null, self::ASSOC_ROWS);
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

}