<?php

namespace Yaoi\Request;

use Yaoi\BaseClass;
use Yaoi\Mappable\Contract;

class Server extends BaseClass implements Contract
{
    public $DOCUMENT_ROOT;
    public $REMOTE_ADDR;
    public $REMOTE_PORT;
    public $SERVER_SOFTWARE;
    public $SERVER_PROTOCOL;
    public $SERVER_NAME;
    public $SERVER_PORT;
    public $REQUEST_URI;
    public $REQUEST_METHOD;
    public $SCRIPT_NAME;
    public $SCRIPT_FILENAME;
    public $PATH_INFO;
    public $PHP_SELF;
    public $HTTP_HOST;
    public $HTTP_USER_AGENT;
    public $HTTP_ACCEPT;
    public $HTTP_ACCEPT_LANGUAGE;
    public $HTTP_ACCEPT_ENCODING;
    public $HTTP_CONNECTION;
    public $HTTP_CACHE_CONTROL;
    public $REQUEST_TIME_FLOAT;
    public $REQUEST_TIME;


    public $argv;
    public $argc;


    static function fromArray(array $row, $object = null, $source = null)
    {
        if (null === $object) {
            $object = new static();
        }
        foreach ($row as $key => $value) {
            $object->$key = $value;
        }
        return $object;
    }

    public function toArray($skipNotSetProperties = false)
    {
        $result = (array)$this;
        if ($skipNotSetProperties) {
            foreach ($result as $key => $value) {
                if (null === $value) {
                    unset($result[$key]);
                }
            }
        }
        return $result;
    }

}