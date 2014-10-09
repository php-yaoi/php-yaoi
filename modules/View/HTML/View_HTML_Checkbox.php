<?php

class View_HTML_Checkbox extends View_HTML_Input {
    public function render() {
        $this->setAttribute('type', 'checkbox');
        if ($this->value) {
            $this->setAttribute('checked', 'checked');
        }
        else {
            $this->setAttribute('checked');
        }
        return parent::render();
    }
} 