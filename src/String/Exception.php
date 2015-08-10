<?php

namespace Yaoi\String;

class Exception extends \Exception
{
    const BAD_DSN = 1;
    const MISSING_QUOTER = 2;
    const PLACEHOLDER_NOT_FOUND = 3;
    const PLACEHOLDER_REDUNDANT = 4;
}