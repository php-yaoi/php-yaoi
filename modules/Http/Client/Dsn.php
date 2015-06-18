<?php

namespace Yaoi\Http\Client;

class Dsn extends \Yaoi\Service\Dsn
{
    public $proxy;
    public $defaultHeaders;
    public $scheme = 'file-get-contents';
    public $log = '';
}