<?php

namespace Yaoi\View\Table;
use Yaoi\View\Table\Stream;

class JIRA extends Stream
{
    public $optionEscapeHTML = false;

    protected $tag = null;

    protected $keys = array();

    protected function renderRow($row)
    {
        if (!$this->keys) {
            echo '||';
            foreach ($row as $key => $value) {
                $keys [] = $key;
                echo ' ', $key, ' || ';
            }
            echo "\n";
        }

        echo '|';
        foreach ($this->keys as $key) {
            $value = array_key_exists($key, $row) ? $row[$key] : '';
            if (null === $value) {
                $value = 'NULL';
            }
            if ($this->optionEscapeHTML) {
                $value = str_replace('<', '&lt;', $value);
            }
            echo ' ', $value, ' |';
        }
        echo "\n";
    }
}