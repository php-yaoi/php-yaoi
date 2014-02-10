<?php

class Log_Driver_Storage implements Log_Driver {
    public function push($message, $type = Log::TYPE_MESSAGE)
    {
    }

    private $storage;

    public function __construct(Log_Dsn $dsn = null)
    {
        $this->storage = Storage::create($dsn->storage);
    }
}