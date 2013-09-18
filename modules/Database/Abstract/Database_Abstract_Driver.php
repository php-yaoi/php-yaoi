<?php

abstract class Database_Abstract_Driver implements Database_Server_Generic {
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
}