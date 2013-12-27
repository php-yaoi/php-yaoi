<?php

class Log_Null extends Log {
    public function __construct(String_Dsn $dsn)
    {
    }

    /**
     * @param $message
     * @return $this
     */
    public function push($message)
    {
    }

} 