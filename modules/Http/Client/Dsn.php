<?php

namespace Yaoi\Http\Client;

class Dsn extends \Yaoi\Client\Dsn
{
    public $proxy;
    public $defaultHeaders;
    public $scheme = 'file-get-contents';
    public $log = '';
}