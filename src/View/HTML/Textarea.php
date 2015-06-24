<?php

namespace Yaoi\View\HTML;

class Textarea extends Element
{
    protected $tag = 'textarea';

    public function setName($name)
    {
        return $this->setAttribute('name', $name);
    }

    public function setValue($value)
    {
        $this->setContent($value);
        return $this;
    }

    public function setPlaceholder($placeholder)
    {
        $this->setAttribute('placeholder', $placeholder);
        return $this;
    }

    public function fillValue(&$source = null)
    {
        if (null === $source) {
            $source = &$_REQUEST;
        }
        if (isset($source[$this->attributes['name']])) {
            $this->setValue($source[$this->attributes['name']]);
        }
        return $this;
    }

}