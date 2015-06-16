<?php

namespace Yaoi\Storage;

use Yaoi\Storage;

class PhpVar extends Storage
{
    public function __construct()
    {
        $this->forceDriver(new Driver\PhpVar());
    }
}