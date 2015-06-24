<?php

namespace Yaoi\Database;

class Exception extends \Exception
{
    const DEFAULT_NOT_SET = 1;
    const CONNECTION_ERROR = 2;
    const WRONG_SERVER_TYPE = 3;
    const NO_DRIVER = 4;
    const QUERY_ERROR = 5;
    const PLACEHOLDER_NOT_FOUND = 6;
    const PLACEHOLDER_REDUNDANT = 7;
    const DISCONNECT_ERROR = 8;
    const REWIND_NOT_SUPPORTED = 9;

    public $query;
}