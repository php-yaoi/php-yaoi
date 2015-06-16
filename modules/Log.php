<?php
namespace Yaoi;

use Yaoi\Log\Driver;
use Yaoi\Client;

/**
 * Class Log
 * @method Driver getDriver()
 * @method static Log getInstance($id = 'default', $reuse = true)
 */
class Log extends Client
{
    protected static $dsnClass = '\Yaoi\Log\Dsn';
    public static $conf = array();
    protected static $instances = array();

    const TYPE_MESSAGE = 'm';
    const TYPE_ERROR = 'e';
    const TYPE_SUCCESS = 's';

    /**
     * @param $message
     * @param string $type
     * @return $this
     */
    public function push($message, $type = Log::TYPE_MESSAGE)
    {
        $this->getDriver()->push($message, $type);
        return $this;
    }
}