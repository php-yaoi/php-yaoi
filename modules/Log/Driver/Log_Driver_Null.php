<?php

class Log_Driver_Null implements Log_Driver {
    public function __construct(Log_Dsn $dsn = null)
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