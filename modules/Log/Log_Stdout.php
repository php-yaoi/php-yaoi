<?php

class Log_Stdout extends Log {
    public function __construct(String_Dsn $dsn)
    {
    }

    /**
     * @param $message
     * @param string $type
     * @return $this
     */
    public function push($message, $type = Log::TYPE_MESSAGE)
    {
        echo print_r($message, 1), "\r\n";
        return $this;
    }

} 