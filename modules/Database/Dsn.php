<?php

namespace Yaoi\Database;

class Dsn extends \Yaoi\Client\Dsn
{
    public $persistent = false;
    public $reconnect = false;
    public $logQueries = false;
    public $unixSocket;
    public $charset;
    public $timezone;
}