<?php

namespace Yaoi\View\Semantic;

use Yaoi\BaseClass;

class Text extends BaseClass
{
    const SUCCESS = 'success';
    const INFO = 'info';
    const ERROR = 'error';
    const TEXT = 'text';
    const HEADING = 'heading';

    public $value;
    public $type = self::TEXT;

    public function __construct($text)
    {
        $this->value = $text;
    }
}