<?php

class View_Table_HTML extends View_Table_Stream {
    public $optionEscapeHTML = false;

    protected $tag = 'table';

    protected $keys = array();

    protected function renderRow($row) {
        if (!$this->keys) {
            echo '<tr>';
            foreach ($row as $key => $value) {
                $this->keys []= $key;
                echo '<th>', $key, '</th>';
            }
            echo '</tr>', "\n";
        }
        echo '<tr>';
        foreach ($this->keys as $key) {
            $value = array_key_exists($key, $row) ? $row[$key] : '';
            if (null === $value) {
                $value = 'NULL';
            }
            if ($this->optionEscapeHTML) {
                $value = str_replace('<', '&lt;', $value);
            }
            echo '<td>', $value, '</td>';
        }
        echo '</tr>', "\n";
    }

}