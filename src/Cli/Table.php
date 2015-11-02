<?php

namespace Yaoi\Cli;

use Yaoi\BaseClass;

class Table extends BaseClass
{

    private $divider = '   ';
    private $rows = array();

    public function addRow(array $row)
    {
        $this->rows [] = $row;
        return $this;
    }


    public function getLines()
    {
        $lines = array();
        $length = array();
        foreach ($this->rows as $row) {
            foreach ($row as $key => $value) {
                $stringLength = strlen($value);
                if (!isset($length[$key]) || $length[$key] < $stringLength) {
                    $length[$key] = $stringLength;
                }
            }
        }

        foreach ($this->rows as $row) {
            $line = '';
            foreach ($length as $key => $maxLength) {
                $value = isset($row[$key]) ? (string)$row[$key] : '';
                $stringLength = strlen($value);
                if ($stringLength < $maxLength) {
                    $value = str_pad($value, $maxLength, ' ');
                }
                if ($line) {
                    $line .= $this->divider;
                }
                $line .= $value;
            }
            $lines []= $line;
        }
        return $lines;
    }
}