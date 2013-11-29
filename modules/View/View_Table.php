<?php

class View_Table {

    public $rows = array();

    public function add($row) {
        $this->rows []= $row;
    }

    public function render() {
        echo '<table>';
        $keys = array();
        foreach ($this->rows as $row) {
            echo '<tr>';
            foreach ($row as $key => $value) {
                $keys []= $key;
                echo '<th>', $key, '</th>';
            }
            echo '</tr>';
            break;
        }
        foreach ($this->rows as $row) {
            echo '<tr>';
            foreach ($keys as $key) {
                echo '<td>', array_key_exists($key, $row) ? $row[$key] : '', '</td>';
            }
            echo '</tr>';
            break;
        }


        echo '</table>';
    }
} 