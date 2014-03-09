<?php

/**
 * Class Log
 * @method Log_Driver getDriver()
 * @method static Log getInstance($id = 'default', $reuse = true)
 */
class Log extends Client {
    const TYPE_MESSAGE = 'm';
    const TYPE_ERROR = 'e';
    const TYPE_SUCCESS = 's';

    /**
     * @param $message
     * @param string $type
     * @return $this
     */
    public function push($message, $type = Log::TYPE_MESSAGE) {
        $this->getDriver()->push($message, $type);
        return $this;
    }
}