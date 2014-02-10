<?php

interface Http_Client_Driver {
    public function reset();
    public function setUrl($url);
    public function setProxy(String_Dsn $proxy);
    public function setMethod($method);
    public function setRequestContent($content);
    public function setHeaders($headers);
    public function fetch();
    public function getResponseContent();
    public function getResponseHeaders();
    public function getRequest();
}