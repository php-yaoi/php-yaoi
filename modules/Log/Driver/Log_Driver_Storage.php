<?php

class Log_Driver_Storage implements Log_Driver {
    public function __construct(Log_Dsn $dsn = null)
    {
        $this->storage = Storage::getInstance($dsn->storage);
    }

    /**
     * @var Storage
     */
    private $storage;

    public function push($message, $type = Log::TYPE_MESSAGE)
    {
        if (is_array($message)) {
            $message_value = array_pop($message);
            $message_key = $message;
        }
        else {
            $message_value = $message;
            $message_key = null;
        }
        $this->storage->set($message_key, $message_value);
        return $this;
    }
}