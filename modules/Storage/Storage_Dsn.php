<?php
use Yaoi\Client\Dsn;

/**
 * Class Storage_Dsn
 */

class Storage_Dsn extends Dsn {
    public $persistent = false;
    public $reconnect = false;
    public $logRequests = false;
    public $unixSocket;
    public $connectionTimeout = 1;
    public $compression;
    public $binary;
    public $instanceId;
    public $ignoreErrors;
    public $proxyClient;
    public $dateSource;
}