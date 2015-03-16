<?php

class Http_Client_Dsn extends Client_Dsn {
    public $proxy;
    public $defaultHeaders;
    public $scheme = 'file-get-contents';
    public $log = '';
} 