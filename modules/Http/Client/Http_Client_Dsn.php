<?php

class Http_Client_Dsn extends String_Dsn {
    public $proxy;
    public $defaultHeaders;
    public $scheme = 'file-get-contents';
} 