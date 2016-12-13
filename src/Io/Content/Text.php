<?php

namespace Yaoi\Io\Content;

use Yaoi\BaseClass;

class Text extends BaseClass implements Element
{
    const SUCCESS = 'success';
    const INFO = 'info';
    const ERROR = 'error';
    const TEXT = 'text';
    const HEADING = 'heading';

    public $value;
    public $type = self::TEXT;

    public $forceLength = false;

    public function length()
    {
        if ($this->forceLength) {
            return $this->forceLength;
        } else {
            return strlen($this->value);
        }
    }

    public function __construct($text)
    {
        $this->value = $text;
    }

    public function __toString()
    {
        return (string)$this->value;
    }
}