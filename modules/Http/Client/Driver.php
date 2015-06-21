<?php

namespace Yaoi\Http\Client;

interface Driver
{
    public function reset();

    public function setUrl($url);

    public function setProxy(Settings $proxy);

    public function setMethod($method);

    public function setRequestContent($content);

    public function setHeaders($headers);

    public function fetch();

    public function getResponseContent();

    public function getResponseHeaders();

    public function getRequest();
}