<?php

class View_JIRATable extends View_HTMLTable {
    public function render() {
        $keys = array();
        foreach ($this->content as $row) {
            if (!$keys) {
                echo '||';
                foreach ($row as $key => $value) {
                    $keys []= $key;
                    echo ' ', $key, ' || ';
                }
                echo "\n";
            }

            echo '|';
            foreach ($keys as $key) {
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

        return $this;
    }

} 