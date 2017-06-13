<?php

namespace Yaoi\Io\Content;


class Progress extends Semantic
{
    public function __construct($done, $total, $text = '', $prefix = '')
    {
        $this->done = $done;
        $this->total = $total;
        $this->text = $text;
        $this->prefix = $prefix;
    }

    public $done;
    public $total;
    public $text;
    public $prefix;
}