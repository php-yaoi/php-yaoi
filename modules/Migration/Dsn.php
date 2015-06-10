<?php

namespace Yaoi\Migration;

use Storage;
use Storage_Dsn;

class Dsn extends \Yaoi\Client\Dsn
{
    /**
     * @var Storage|Storage_Dsn|string
     */
    public $storage;

    /**
     * Closure gets owner Migration_Client as argument
     * @var Closure
     */
    public $run;
}