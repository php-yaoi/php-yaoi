<?php

class View_HTML_Select extends View_HTML_Input {
    protected $tag = 'select';
    protected $options = array();

    public function setOptions($options) {
        $this->options = $options;
        return $this;
    }

    public function render() {
        $this->renderHead();
        echo '>';
        $this->contentExists = true;
        foreach ($this->options as $value => $title) {
            echo '<option value="', self::escapeValue($value),
            ($this->value !== null && $this->value == $value ? '" selected="selected' : ''),
            '">', self::escapeContent($title), '</option>';
        }
        $this->renderTail();
    }

}