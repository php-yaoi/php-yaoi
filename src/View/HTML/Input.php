<?php

namespace Yaoi\View\HTML;

class Input extends Element
{
    protected $tag = 'input';
    protected $value;

    public function setName($name)
    {
        return $this->setAttribute('name', $name);
    }

    public function setValue($value)
    {
        $this->value = $value;
        $this->setAttribute('value', $value);
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
        if (is_object($source)) {
            $field = $this->attributes['name'];
            if (property_exists($source, $field) || isset($source->$field)) {
                $this->setValue($source->$field);
            }
        } elseif (isset($source[$this->attributes['name']])) {
            $this->setValue($source[$this->attributes['name']]);
        }
        return $this;
    }


    const TYPE_TEXT = 'text';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RADIO = 'radio';
    const TYPE_HIDDEN = 'hidden';

    public function setType($type)
    {
        $this->setAttribute('type', $type);
        return $this;
    }


    public function hidden()
    {
        $this->setAttribute('type', self::TYPE_HIDDEN);
        return $this;
    }
}