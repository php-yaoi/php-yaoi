<?php

namespace Yaoi\Io\Content;

class Anchor extends Text
{
    public $anchor;

    public function __construct($text, $anchor = null) {
        parent::__construct($text);
        $this->anchor = $anchor;
    }
}