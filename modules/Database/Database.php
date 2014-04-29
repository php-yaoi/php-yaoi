<?php

/**
 * TODO catch and repair crashed table
 * TODO reconnect on gone away
 *
 * Class Database
 */
class Database extends Client implements Database_Interface {
    public static $conf = array();
    protected static $instances = array();

    /**
     * @param null $statement
     * @param null $binds
     * @return Database_Query
     */
    public function query($statement = null, $binds = null) {
        if (func_num_args() > 2) {
            $arguments = func_get_args();
            array_shift($arguments);
            $binds = $arguments;
        }
        if (null !== $binds && !is_array($binds)) {
            $binds = array($binds);
        }
        $query = new Database_Query($statement, $binds, $this->getDriver());
        $query->setDatabaseClient($this);
        if (null !== $this->log) {
            $query->log($this->log);
        }

        return $query;
    }


    public function mappableInsertString(Mappable $item) {
        $l = array_map(array($this, 'quote'), $item->toArray());
        return "(".implode(',', array_keys($l)).") VALUES (" . implode(",", $l) . ")";
    }


    public function quote($s) {
        return $this->getDriver()->quote($s);
    }


    protected $originalDriver;

    /**
     * @param Mock_DataSet $dataSet
     * @return Database
     */
    public function mock(Mock_DataSet $dataSet = null)
    {
        if ($dataSet instanceof Mock_DataSetPlay) {
            $driver = new Database_Mock_DriverPlay();
            if (null === $this->originalDriver) {
                $this->originalDriver = $this->getDriver();
            }
            $driver->mock($dataSet);
            $this->forceDriver($driver);
        }
        elseif ($dataSet instanceof Mock_DataSetCapture) {
            $driver = new Database_Mock_DriverCapture();
            if ($this->originalDriver) {
                $this->forceDriver($this->originalDriver);
            }
            $driver->setOriginalDriver($this->getDriver());
            if (null === $this->originalDriver) {
                $this->originalDriver = $this->getDriver();
            }
            $driver->mock($dataSet);
            $this->forceDriver($driver);
        }
        elseif (null === $dataSet && null !== $this->originalDriver) {
            $this->forceDriver($this->originalDriver);
            $this->originalDriver = null;
        }
        return $this;
    }

    /**
     * @var Log
     */
    private $log;
    public function log(Log $log = null) {
        $this->log = $log;
        return $this;
    }


    /**
     * @param $statement
     * @param array $binds
     * @return mixed|string
     * @throws Client_Exception
     * @throws Database_Exception
     */
    public function buildString($statement, array $binds) {
        $replace = array();
        $unnamed = true;
        $i = 0;

        // check binds array type
        foreach ($binds as $key => $value) {
            if ($unnamed && $key !== $i++) {
                $unnamed = false;
                break;
            }
        }

        $driver = $this->getDriver();

        if ($unnamed) {
            $pos = 0;
            foreach ($binds as $value) {
                $pos = strpos($statement, '?', $pos);
                if ($pos !== false) {
                    if ($value instanceof Sql_Expression) {
                        $value = '(' . $value->build($this) . ')';
                    }
                    else {
                        $value = $driver->quote($value);
                    }
                    $statement = substr_replace($statement, $value, $pos, 1);
                    $pos += strlen($value);
                } else {
                    throw new Database_Exception('Placeholder \'?\' not found', Database_Exception::PLACEHOLDER_NOT_FOUND);
                }
            }

            if (strpos($statement, '?', $pos) !== false) {
                throw new Database_Exception('Redundant placeholder: "' . $statement . '", binds: '
                    . var_export($binds),
                    Database_Exception::PLACEHOLDER_REDUNDANT);
            }

            return $statement;
        } else {
            foreach ($binds as $key => $value) {
                if ($value instanceof Sql_Expression) {
                    $value = '(' . $value->build($this) . ')';
                }
                else {
                    $value = $driver->quote($value);
                }

                $replace [':' . $key] = $value;
            }
            return strtr($statement, $replace);
        }
    }

    /**
     * @param $expression
     * @param null $binds
     * @return Sql_Expression
     * @throws Sql_Exception
     */
    public function expr($expression, $binds = null) {
        return Sql_Expression::createFromFuncArguments(func_get_args());
    }


    /**
     * @param null $from
     * @return Sql_Select
     */
    public function select($from = null) {
        return Sql_Select::create($from)->bindDatabase($this);
    }

}