<?php

namespace Yaoi\Command;

use Yaoi\Command;
use Yaoi\Io\Request;

interface Runner
{
    public function __construct(Command $command);

    public function run(Request $request = null);
}