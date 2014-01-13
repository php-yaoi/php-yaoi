<?php

class Log_Stdout extends Log {
    public function __construct(String_Dsn $dsn)
    {
    }

    public function push($message)
    {
        echo print_r($message, 1), "\r\n";
    }

} 