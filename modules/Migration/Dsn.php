<?php

namespace Yaoi\Migration;

use Yaoi\Storage;

class Dsn extends \Yaoi\Client\Dsn
{
    /**
     * @var \Yaoi\Storage|\Yaoi\Storage\Dsn|string
     */
    public $storage;

    /**
     * Closure gets owner Migration_Client as argument
     * @var \Closure
     */
    public $run;
}