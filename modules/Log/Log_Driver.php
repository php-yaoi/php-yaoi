<?php

interface Log_Driver {
    public function push($message, $type = Log::TYPE_MESSAGE);
    public function __construct(Log_Dsn $dsn = null);
} 