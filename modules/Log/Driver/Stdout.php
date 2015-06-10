<?php

namespace Yaoi\Log\Driver;

use Yaoi\Log;
use Yaoi\Log\Driver;
use Yaoi\Log\Dsn;

class Stdout implements Driver
{
    private $dsn;

    public function __construct(Dsn $dsn = null)
    {
        $this->dsn = null === $dsn ? new Dsn : $dsn;
    }

    /**
     * @param $message
     * @param string $type
     * @return $this
     */
    public function push($message, $type = Log::TYPE_MESSAGE)
    {
        echo $this->dsn->prefix, print_r($message, 1), "\r\n";
        return $this;
    }

}