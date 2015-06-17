<?php

namespace Yaoi\Log\Driver;

use Yaoi\Log;
use Yaoi\Log\Driver;
use Yaoi\Log\Dsn;

class Void implements Driver
{
    public function __construct(Dsn $dsn = null)
    {
    }

    /**
     * @param $message
     * @param string $type
     * @return $this
     */
    public function push($message, $type = Log::TYPE_MESSAGE)
    {
        return $this;
    }

}