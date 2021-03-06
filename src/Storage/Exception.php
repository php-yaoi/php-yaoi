<?php

namespace Yaoi\Storage;

class Exception extends \Exception
{
    const BAD_SERIALIZED_DATA = 1;
    const DEFAULT_NOT_SET = 2;
    const EXPORT_ARRAY_NOT_SUPPORTED = 3;
    const CONNECTION_FAILED = 4;
    const SET_FAILED = 5;
    const GET_FAILED = 6;
    const SCALAR_REQUIRED = 7;
    const PROXY_REQUIRED = 8;
} 