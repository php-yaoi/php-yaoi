<?php

class View_Table {
    public $optionEscapeHTML = false;

    public $rows = array();

    public function add($row) {
        $this->rows []= $row;
    }

    public function render() {
        echo '<table>', "\n";
        $keys = array();
        foreach ($this->rows as $row) {
            if (!$keys) {
                echo '<tr>';
                foreach ($row as $key => $value) {
                    $keys []= $key;
                    echo '<th>', $key, '</th>';
                }
                echo '</tr>', "\n";
            }

            echo '<tr>';
            foreach ($keys as $key) {
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


        echo '</table>';
    }
} 