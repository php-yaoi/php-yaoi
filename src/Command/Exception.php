<?php

namespace Yaoi\Command;


class Exception extends \Exception
{
    const UNKNOWN_OPTION = 1;
    const ALREADY_DEFINED = 2;
    const INVALID_VALUE = 3;
}