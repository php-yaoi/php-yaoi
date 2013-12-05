<?php

interface Http_ClientDriver_Interface {
    public function setUrl($url);
    public function setMethod($method);
    public function setRequestContent($content);
    public function setHeaders($headers);
    public function fetch();
    public function getResponseContent();
    public function getResponseHeaders();
    public function getRequest();
}