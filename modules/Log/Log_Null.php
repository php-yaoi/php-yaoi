<?php

class Log_Null extends Log {
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
        return $this;
    }

} 