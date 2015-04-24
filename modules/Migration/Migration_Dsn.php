<?php

class Migration_Dsn extends Client_Dsn {
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