<?php
namespace Yaoi\Service;

class Exception extends \Exception
{
    const NO_FALLBACK = 1;
    const NO_DRIVER = 2;
    const NO_ANSWER = 3;
    const BAD_ANSWER = 4;
    const SETTINGS_REQUIRED = 5;
    const INVALID_ARGUMENT = 6;
}