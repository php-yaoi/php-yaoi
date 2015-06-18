<?php

namespace Yaoi;

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

use Yaoi\Database;
use Yaoi\Date\Source;
use Yaoi\Log;
use Yaoi\Storage;

class App extends Service {
    const MODE_CLI = 'cli';
    const MODE_HTTP = 'http';

    public $host;
    public $path;
    public $mode;

    public $logPath;
    public $logErrors = true;

    /**
     * @var \Yaoi\App\Conf
     */
    protected $conf;

    public function __construct($dsn = null) {
        parent::__construct($dsn);
    }

    public static function init($conf = null) {
        // TODO properly detect CLI mode and show "Usage" message by default
        if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
            self::$instance->path = $_SERVER['REQUEST_URI'];
            self::$instance->host = $_SERVER['HTTP_HOST'];
            self::$instance->mode = self::MODE_HTTP;
        }
        elseif (isset($_SERVER['argv'][1])) {
            self::$instance->path = $_SERVER['argv'][1];
            if (isset($_SERVER['argv'][2])) {
                self::$instance->host = $_SERVER['argv'][2];
            }
            self::$instance->mode = self::MODE_CLI;
        }

        return self::$instance;
    }


    function route($path = null, $host = null) {
        throw new \Exception('No routes');
    }


    protected static $instance;

    /**
     * @return App
     * @throws \Exception
     */
    static function instance() {
        return self::$instance;
    }


    private static $resources = array();

    /**
     * @param string $identifier
     * @return Database\Contract
     */
    static function database($identifier = 'default') {
        return Database::getInstance($identifier);
    }

    static function cache($identifier = 'default') {
        return Storage::getInstance($identifier);
    }

    /**
     * @param string $identifier
     * @return Source
     */
    static function time($identifier = 'default') {
        $resource = &self::$resources['time_' . $identifier];
        if (!isset($resource)) {
            $resource = new Source();
        }
        return $resource;
    }


    /**
     * @param string $identifier
     * @return Log
     */
    static function log($identifier = 'default') {
        return Log::getInstance($identifier);
    }

    public function stop($errorCode = 0) {
        die($errorCode);
    }

    public static function redirect($url, $permanent = false, $stop = true) {
        if ($permanent) {
            header('HTTP/1.1 301 Moved Permanently');
        }
        header('Location: ' . $url);
        if ($stop) {
            App::instance()->stop();
        }
    }
}

