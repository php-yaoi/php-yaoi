<?php

namespace Yaoi\Database;

use Iterator;
use Sql_DefaultValue;
use Sql_Expression;
use Sql_Symbol;

abstract class Driver implements \Yaoi\Database\Driver\Contract
{
    /**
     * @var Dsn
     */
    public $dsn;

    public function __construct(Dsn $dsn = null)
    {
        $this->dsn = $dsn;
    }

    public function quote($value)
    {
        if (null === $value) {
            return 'NULL';
        } elseif (is_int($value)) {
            return (string)$value;
        } elseif (is_float($value)) {
            //return rtrim(rtrim(sprintf('%.14F', $value), '0'), '.');
            return rtrim(rtrim(sprintf('%F', $value), '0'), '.');
        } elseif (is_array($value) || $value instanceof Iterator) {
            $result = '';
            foreach ($value as $item) {
                $result .= $this->quote($item) . ', ';
            }
            return substr($result, 0, -2);
        } elseif ($value instanceof Sql_Expression) {
            return '(' . $value->build($this) . ')';
        } elseif ($value instanceof Sql_Symbol) {
            return $this->quoteSymbol($value);
        } elseif ($value instanceof Sql_DefaultValue) {
            return 'DEFAULT';
        } else {
            return "'" . $this->escape($value) . "'";
        }
    }

    public function quoteSymbol(Sql_Symbol $symbol)
    {
        $result = '';
        foreach ($symbol->names as $name) {
            $result .= '"' . $name . '".';
        }
        if ($result) {
            $result = substr($result, 0, -1);
        }

        return $result;
    }
}