<?php

class Http_Client_Driver_FileGetContents implements Http_Client_Driver {
    private $context = array();

    private $url;
    private $responseHeaders = array();
    private $responseResult;

    public function __construct() {
        $this->reset();
    }

    public function reset() {
        $this->context = array(
            'http' => array(
                'method' => 'GET',
                //'protocol_version' => 1.1,
                'ignore_errors' => true,
                //'timeout' => 5,
                'follow_location' => false, //$this->followLocation, // don't or do follow redirects
                'header' => '',
            ),
        );
    }


    public function setUrl($url) {
        $this->url = $url;
    }

    public function setProxy(String_Dsn $proxy) {
        $scheme = $proxy->scheme;
        if (!$scheme || 'http' === $scheme) {
            $scheme = 'tcp';
        }
        $this->context['http']['proxy'] = $scheme . '://' . $proxy->hostname
            . ($proxy->port ? ':' . $proxy->port : '');
        $this->context['http']['request_fulluri'] = true;
        if ($proxy->username) {
            $this->context['http']['header'] .= "Proxy-Authorization: Basic "
                . base64_encode($proxy->username . ':' . $proxy->password) . "\r\n";
        }
    }

    public function setMethod($method) {
        $this->context['http']['method'] = $method;
    }

    public function setRequestContent($content) {
        $this->context['http']['content'] = $content;

        //$this->requestContent = $content;
    }

    public function setHeaders($headers) {
        foreach ($headers as $type => $value) {
            $this->context['http']['header'] .= $type . ': ' . $value . "\r\n";
        }
    }

    public function fetch() {
        $ctx = stream_context_create($this->context);
        //var_export($this->url);
        //var_export($this->context);
        if (!$this->url) {
            throw new Http_Client_Exception('Empty url', Http_Client_Exception::EMPTY_URL);
        }
        $this->responseResult = @file_get_contents($this->url, false, $ctx);
        //echo $this->responseResult;
        //die();
        $this->responseHeaders = array();
        if (isset($http_response_header)) {
            foreach ($http_response_header as $hdr) {
                $this->responseHeaders []= $hdr;
            }
        }
    }

    public function getResponseContent() {
        return $this->responseResult;
    }

    public function getResponseHeaders() {
        return $this->responseHeaders;
    }

    public function getRequest() {
        return $this->context;
    }

} 