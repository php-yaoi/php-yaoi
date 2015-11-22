<?php

namespace Yaoi\Cli;


class Exception extends \Yaoi\Command\Exception
{
    const NON_TAILING_VARIADIC_ARGUMENT = 10;
    const NON_TAILING_OPTIONAL_ARGUMENT = 11;
    const ARGUMENT_REQUIRED = 12;
    const VALUE_REQUIRED = 13;
    const OPTION_REQUIRED = 14;
}