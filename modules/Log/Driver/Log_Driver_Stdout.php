<?php

class Log_Driver_Stdout implements Log_Driver {
    private $dsn;

    public function __construct(Log_Dsn $dsn = null)
    {
        $this->dsn = null === $dsn ? new Log_Dsn : $dsn;
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