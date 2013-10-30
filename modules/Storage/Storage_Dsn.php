<?php

/**
 * Class Storage_Dsn
 */

class Storage_Dsn extends String_Dsn {
    public $persistent = false;
    public $reconnect = false;
    public $logRequests = false;
    public $unixSocket;
}