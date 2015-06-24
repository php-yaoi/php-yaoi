<?php

namespace Yaoi\View\HTML;

use Traversable;
use Yaoi\View\Exception;

class Select extends Input
{
    protected $tag = 'select';
    protected $options = array();

    public function isEmpty()
    {
        return empty($this->options);
    }

    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception('Traversable or array required', Exception::WRONG_DATA_TYPE);
        }
        $this->options = $options;
        return $this;
    }

    public function render()
    {
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