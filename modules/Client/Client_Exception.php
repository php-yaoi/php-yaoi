<?php
namespace Yaoi\Client;

class Exception extends \Exception
{
    const DEFAULT_NOT_SET = 1;
    const NO_DRIVER = 2;
    const NO_ANSWER = 3;
    const BAD_ANSWER = 4;
    const DSN_REQUIRED = 5;
    const INVALID_ARGUMENT = 6;
}