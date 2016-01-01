<?php

namespace Yaoi\Io\Content;

class Rows {
    /** @var \Iterator */
    private $iterator;

    public function __construct(\Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    public function getIterator() {
        return $this->iterator;
    }
}