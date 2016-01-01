<?php

namespace Yaoi\Cli\View;

use Yaoi\View\Hardcoded;
use Yaoi\Io\Content\Renderer;
use Yaoi\Io\Content;

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

    private $showHeader = false;
    public function setShowHeader($yes = true) {
        $this->showHeader = $yes;
        return $this;
    }

    public function render()
    {

        $length = array();
        $rowDelimiterLine = null;
        $lines = array();

        $rows = array(0 => array());
        $keys = array();
        foreach ($this->rows as $rowIndex => $row) {
            $rows [] = $row;

            foreach ($row as $key => $value) {
                $keys [$key] = $key;
            }
        }
        if ($this->showHeader) {
            $rows[0] = $keys;
        }

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $key => $value) {
                if (!$value instanceof Content\Text) {
                    $value = new Content\Text($value);
                }
                $renderer = new Text($value);
                foreach ($renderer->lines() as $lineIndex => $line) {
                    $stringLength = strlen($line->text->value);
                    if (!isset($length[$key]) || $length[$key] < $stringLength) {
                        $length[$key] = $stringLength;
                    }
                    $lines [$rowIndex][$lineIndex][$key] = $line;
                }
            }
        }


        foreach ($lines as $rowIndex => $rowData) {
            foreach ($rowData as $lineIndex => $row) {
                $line = '';
                foreach ($length as $key => $maxLength) {
                    /** @var \Yaoi\Cli\View\Text $value */
                    $value = isset($row[$key]) ? $row[$key] : null;

                    if ($line) {
                        $line .= $this->colDelimiter;
                    }
                    if ($value) {
                        $value->strPad($maxLength);
                        $line .= $value;
                    }
                    else {
                        $line .= str_repeat(' ', $maxLength);
                    }
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
}