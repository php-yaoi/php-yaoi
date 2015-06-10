<?php

namespace Yaoi\Log\Driver;

use \Storage;
use Yaoi\Log;
use Yaoi\Log\Driver;
use Yaoi\Log\Dsn;

class Storage implements Driver
{
    public function __construct(Dsn $dsn = null)
    {
        $this->storage = \Storage::getInstance($dsn->storage);
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
        } else {
            $message_value = $message;
            $message_key = null;
        }
        $this->storage->set($message_key, $message_value);
        return $this;
    }
}