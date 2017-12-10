<?php

namespace Yaoi\Io;

abstract class Response
{
    const STATUS_OK = 200;
    const STATUS_NOT_FOUND = 404;
    const STATUS_BAD_REQUEST = 400;
    const STATUS_FORBIDDEN = 403;

    public $status;

    abstract public function error($message);

    abstract public function success($message);

    abstract public function addContent($message);
}