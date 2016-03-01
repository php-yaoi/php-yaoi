<?php

namespace Yaoi\Command;


class Exception extends \Exception
{
    const UNKNOWN_OPTION = 1;
    const ALREADY_DEFINED = 2;
    const INVALID_VALUE = 3;
    const INVALID_ARGUMENT = 4;
    const OPTION_REQUIRED = 14;
    const ARGUMENT_REQUIRED = 12;
    const VALUE_REQUIRED = 13;
}