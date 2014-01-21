<?php

class Http_Client_Exception extends Exception {
    const BAD_REQUEST = 1;
    const EMPTY_URL = 2;

    public $request;
    public $url;
    public $responseHeaders;
} 