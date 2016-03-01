<?php

namespace Yaoi\Cli;


class Exception extends \Yaoi\Command\Exception
{
    const NON_TAILING_VARIADIC_ARGUMENT = 10;
    const NON_TAILING_OPTIONAL_ARGUMENT = 11;
}