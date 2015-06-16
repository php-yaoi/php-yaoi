<?php
namespace Yaoi\Storage;

/**
 * Class Storage_Dsn
 */
class Dsn extends \Yaoi\Client\Dsn
{
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