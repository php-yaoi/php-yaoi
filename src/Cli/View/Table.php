<?php

namespace Yaoi\Cli\View;

use Yaoi\View\Hardcoded;
use Yaoi\View\Semantic\Renderer;
use Yaoi\View\Semantic\Semantic;

class Table extends Hardcoded implements Renderer
{
    public $divider = '   ';
    /** @var \Yaoi\View\Semantic\Rows  */
    private $rows;

    public function __construct(Semantic $rows)
    {
        $this->rows = $rows;
    }

    public function render()
    {
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
            echo $line, PHP_EOL;
        }
    }
}