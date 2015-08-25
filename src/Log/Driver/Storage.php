<?php

namespace Yaoi\Log\Driver;

use Yaoi\BaseClass;
use Yaoi\Log;
use Yaoi\Log\Driver;
use Yaoi\Log\Settings;

class Storage extends BaseClass implements Driver
{
    public function __construct(Settings $dsn = null)
    {
        $this->storage = \Yaoi\Storage::getInstance($dsn->storage);
    }

    /**
     * @var \Yaoi\Storage
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