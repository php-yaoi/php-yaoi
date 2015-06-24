<?php

namespace Yaoi\Database;

class Settings extends \Yaoi\Service\Settings
{
    public $persistent = false;
    public $reconnect = false;
    public $logQueries = false;
    public $unixSocket;
    public $charset;
    public $timezone;
}