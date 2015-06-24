<?php

namespace Yaoi\Http\Client;

class Settings extends \Yaoi\Service\Settings
{
    public $proxy;
    public $defaultHeaders;
    public $scheme = 'file-get-contents';
    public $log = '';
}