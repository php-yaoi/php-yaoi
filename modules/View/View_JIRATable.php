<?php

class View_JIRATable extends View_HTMLTable {
    public $rows = array();
    public function add($row)
    {
        // TODO: Implement add() method.
    }

    public function setRows(&$rows)
    {
        $this->rows = $rows;
        return $this;
    }


    public function render() {
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

            echo '| ';
            foreach ($keys as $key) {
                $value = array_key_exists($key, $row) ? $row[$key] : '';
                if (null === $value) {
                    $value = 'NULL';
                }
                if ($this->optionEscapeHTML) {
                    $value = str_replace('<', '&lt;', $value);
                }
                echo $value, ' | ';
            }
            echo ' |', "\n";
        }

        return $this;
    }

} 