<?php

class Http_ClientException extends Exception {
    const BAD_REQUEST = 1;

    public $context;
    public $url;
    public $responseHeaders;
} 