<?php

class Http_ClientException extends Exception {
    const BAD_REQUEST = 1;

    public $request;
    public $url;
    public $responseHeaders;
} 