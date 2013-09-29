<?php

abstract class Database_Abstract_Driver implements Database_Server_Generic, Mock_Able {
    /**
     * @var Database_Dsn
     */
    protected $dsn;
    public function __construct(Database_Dsn $dsn) {
        $this->dsn = $dsn;
    }

    public function quote($value) {
        if (null === $value) {
            return 'NULL';
        }
        elseif (is_int($value)) {
            return (string)$value;
        }
        elseif (is_float($value)) {
            return sprintf('%F', $value);
        }
        elseif (is_array($value) || $value instanceof Iterator) {
            $result = '';
            foreach ($value as $item) {
                $result .= $this->quote($item) . ', ';
            }
            return substr($result, 0, -2);
        }
        else {
            return "'" . $this->escape($value) . "'";
        }
    }


    /**
     * @var Mock_DataSet
     */
    protected $mockDataSet;
    public function mock(Mock_DataSet $dataSet = null) {
        if (null === $dataSet) {
            return $this->mockDataSet;
        }
        else {
            $this->mockDataSet = $dataSet;
            return $this->mockDataSet;
        }
    }



    abstract protected function performQuery($statement);
    public function query($statement)
    {
        if (null !== $this->mockDataSet && $this->mockDataSet->playActive()) {
            return $this->mockDataSet->get($statement);
        }
        else {
            $res = $this->performQuery($statement);
        }
    }

    public function lastInsertId($res)
    {

    }


    abstract protected function performRowsAffected();
    public function rowsAffected($res)
    {

    }

    abstract protected function performEscape($value);
    public function escape($value)
    {
        return $this->performEscape($value);
    }


}