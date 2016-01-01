<?php

namespace Yaoi\Io\Content;

use Yaoi\BaseClass;

class SubContent extends BaseClass
{
    public $content;
    public function __construct($content) {
        $this->content = $content;
    }
}