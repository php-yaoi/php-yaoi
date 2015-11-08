<?php

namespace Yaoi\Cli\View;

use Yaoi\View\Hardcoded;
use Yaoi\View\Semantic\Renderer;

class Table extends Hardcoded implements Renderer
{
    private $colDelimiter = '   ';
    private $rowDelimiter = null;
    /** @var \Iterator  */
    private $rows;

    public function __construct(\Iterator $rows)
    {
        $this->rows = $rows;
    }

    public function setColDelimiter($delimiter = '   ') {
        $this->colDelimiter = $delimiter;
        return $this;
    }

    public function setRowDelimiter($delimiter = null) {
        $this->rowDelimiter = $delimiter;
        return $this;
    }

    public function render()
    {
        $length = array();
        $rowDelimiterLine = null;
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
                    $line .= $this->colDelimiter;
                }
                $line .= $value;
            }
            echo $line, PHP_EOL;
            if ($this->rowDelimiter) {
                if (null === $rowDelimiterLine) {
                    $lineLength = strlen($line);
                    $repeat = ceil($lineLength / strlen($this->rowDelimiter));
                    $rowDelimiterLine = str_repeat($this->rowDelimiter, $repeat);
                    if (strlen($rowDelimiterLine) > $lineLength) {
                        $rowDelimiterLine = substr($rowDelimiterLine, 0, $lineLength);
                    }
                }
                echo $rowDelimiterLine, PHP_EOL;
            }
        }
    }
}