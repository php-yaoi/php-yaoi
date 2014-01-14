<?php

abstract class Database_Driver implements Database_Server_Generic {
    const RESULT = 'result';
    const LAST_INSERT_ID = 'lid';
    const QUERY = 'query';
    const ESCAPE = 'escape';
    const ERROR_MESSAGE = 'error';
    const ASSOC_ROWS = 'assoc_rows';
    const REWIND = 'rewind';
    const ROWS_AFFECTED = 'rows_affected';

    /**
     * @var Database_Dsn
     */
    protected $dsn;
    public function __construct(Database_Dsn $dsn = null) {
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
            //return rtrim(rtrim(sprintf('%.14F', $value), '0'), '.');
            return rtrim(rtrim(sprintf('%F', $value), '0'), '.');
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