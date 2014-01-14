<?php

class View_HTMLTable extends View_TableRenderer {
    public $optionEscapeHTML = false;

    protected $tag = 'table';

    protected $keys = array();
    public function push($row) {
        ob_start();
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
        $tr = ob_get_contents();
        ob_end_clean();
        $this->renderContentChunk($tr);
    }

    public function render() {
        $this->renderHead();

        foreach ($this->rows as $row) {
            $this->push($row);
        }

        $this->renderTail();
        return $this;
    }
} 