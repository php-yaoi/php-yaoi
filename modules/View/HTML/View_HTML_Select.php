<?php

class View_HTML_Select extends View_HTML_Input {
    protected $tag = 'select';
    protected $options = array();

    public function isEmpty() {
        return empty($this->options);
    }

    public function setOptions($options) {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new View_Exception('Traversable or array required', View_Exception::WRONG_DATA_TYPE);
        }
        $this->options = $options;
        return $this;
    }

    public function render() {
        if (isset($this->attributes['value'])) {
            unset($this->attributes['value']);
        }

        $this->renderHead();
        echo '>';
        $this->contentRendered = true;
        foreach ($this->options as $value => $title) {
            echo '<option value="', self::escapeValue($value),
            ($this->value !== null && $this->value == $value ? '" selected="selected' : ''),
            '">', self::escapeContent($title), '</option>';
        }
        $this->renderTail();
    }

}