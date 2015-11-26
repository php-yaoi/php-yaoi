<?php

namespace Yaoi\Log\Driver;

use Yaoi\Log;
use Yaoi\Log\Driver;
use Yaoi\Log\Settings;

class Stdout implements Driver
{
    protected $dsn;

    public function __construct(Settings $dsn = null)
    {
        $this->dsn = null === $dsn ? new Settings : $dsn;
    }

    /**
     * @param $message
     * @param string $type
     * @return $this
     */
    public function push($message, $type = Log::TYPE_MESSAGE)
    {
        if (is_object($message) && method_exists($message, '__toString')) {
            $message = (string)$message;
        }
        echo $this->dsn->prefix, print_r($message, 1), "\n";
        return $this;
    }

}