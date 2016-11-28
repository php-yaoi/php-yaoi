<?php

namespace Yaoi\Io\Content;


class Progress extends Semantic
{
    public function __construct($done, $total, $text = '')
    {
        $this->done = $done;
        $this->total = $total;
        $this->text = $text;
    }

    public $done;
    public $total;
    public $text;
}