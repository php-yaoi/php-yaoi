<?php
namespace Yaoi\Storage;
use Yaoi\Service;

class Settings extends \Yaoi\Service\Settings
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
    public $dateSource = Service::PRIMARY;
}