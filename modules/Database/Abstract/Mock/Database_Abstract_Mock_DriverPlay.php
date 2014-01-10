<?php

abstract class Database_Abstract_Mock_DriverPlay extends Database_Driver implements Mock_Able {
    public function query($statement)
    {
        $queryMock = $this->mock->branch2(self::QUERY, $statement);
        return $queryMock;
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     */
    public function lastInsertId($queryMock)
    {
        return $queryMock->get2(self::LAST_INSERT_ID);
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     */
    public function rowsAffected($queryMock)
    {
        return $queryMock->get2(self::ROWS_AFFECTED);
    }


    public function escape($value)
    {
        return $this->mock->branch2(self::ESCAPE)->get2($value);
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     */
    public function rewind($queryMock)
    {
        return $queryMock->branch2(self::REWIND)->get2();
    }

    /**
     * @param Mock_DataSetPlay $queryMock
     */
    public function fetchAssoc($queryMock)
    {
        return $queryMock->branch2(self::ASSOC_ROWS)->get2();
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
        return $queryMock->get2(self::ERROR_MESSAGE);
    }

}