<?php

abstract class Log {
    public static $conf = array();

    /**
     * @param string $id
     * @param null $originalId
     * @return Log
     * @throws Client_Exception
     */
    public static function createById($id = 'default', $originalId = null) {
        if (isset(Log::$conf[$id])) {
            $dsn = new String_Dsn(Log::$conf[$id]);
            if ($originalId) {
                $dsn->originalId = $originalId;
            }
            $resource = self::createByDsn($dsn);
        }
        elseif ('default' == $id) {
            throw new Client_Exception('Default log not configured', Client_Exception::DEFAULT_NOT_SET);
        }
        else {
            $resource = self::createById('default', $id);
        }
        return $resource;
    }


    /**
     * @param String_Dsn $dsn
     * @return Log
     * @throws Client_Exception
     */
    public static function createByDsn($dsn) {
        if (!$dsn instanceof String_Dsn) {
            $dsn = new String_Dsn($dsn);
        }

        $driverClass = 'Log_' . String_Utils::toCamelCase($dsn->scheme, '-');
        if (!class_exists($driverClass)) {
            throw new Client_Exception('Driver for ' . $dsn->scheme . ' not found', Client_Exception::NO_DRIVER);
        }
        $resource = new $driverClass($dsn);
        return $resource;
    }


    abstract public function __construct(String_Dsn $dsn);

    /**
     * @param $message
     * @return $this
     */
    abstract public function push($message);

}