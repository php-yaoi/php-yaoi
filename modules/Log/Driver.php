<?php

namespace Yaoi\Log;

use Yaoi\Log\Dsn;
use Yaoi\Log;

interface Driver
{
    public function push($message, $type = Log::TYPE_MESSAGE);

    public function __construct(Dsn $dsn = null);
}