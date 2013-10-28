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



    abstract protected function executeQuery($statement);
    abstract protected function executeLastInsertId($result);
    abstract protected function executeEscape($string);
    abstract protected function executeFetchAssoc($result);
    abstract protected function executeRewind($result);


    public function query($statement) {
        if ($this->mockDataSet)
        if (Mock_Able::MOCK_PLAY == $this->mockStatus) {
            return $this->mockDataSet->get($statement);
        }
        else {
            return $this->executeQuery($statement);
        }
    }

    public function fetchAssoc($result) {
        if ($result instanceof Mock_DataSet) {

        }
        else {
            $row = $this->executeFetchAssoc($result);
            if (Mock_Able::MOCK_CAPTURE == $this->mockStatus) {

            }
        }
    }



    /**
     * @var Mock_DataSet
     */
    protected $mockDataSet;

    public function setMock(Mock_DataSet $dataSet = null)
    {
        $this->mockDataSet = $dataSet;
    }


}