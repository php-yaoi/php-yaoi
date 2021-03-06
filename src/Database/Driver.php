<?php

namespace Yaoi\Database;

use Iterator;
use Yaoi\BaseClass;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Sql\DefaultValue;
use Yaoi\Sql\Expression;
use Yaoi\Sql\Raw;
use Yaoi\Sql\Statement;
use Yaoi\Sql\Symbol;

abstract class Driver extends BaseClass implements \Yaoi\Database\Driver\Contract
{
    /**
     * @var Settings
     */
    public $dsn;

    public function __construct(Settings $dsn = null)
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
        } elseif ($value instanceof Statement) {
            return '(' . $value->build($this) . ')';
        } elseif ($value instanceof Expression) {
            return $value->isStatement() ? '(' . $value->build($this) . ')' : $value->build($this);
        } elseif ($value instanceof Symbol) {
            return $this->quoteSymbol($value);
        } elseif ($value instanceof Raw) {
            return $value->value;
        } elseif ($value instanceof Column) {
            return $this->quoteSymbol(new Symbol($value->table->alias ?: $value->table->schemaName, $value->schemaName));
        } elseif ($value instanceof Table) {
            return $this->quoteSymbol(new Symbol($value->schemaName))
            . ($value->alias
                ? ' AS ' . $this->quoteSymbol(new Symbol($value->alias))
                : '');
        } elseif ($value instanceof DefaultValue) {
            return 'DEFAULT';
        } else {
            return "'" . $this->escape($value) . "'";
        }
    }

    public function quoteSymbol(Symbol $symbol)
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