<?php

abstract class Abstract_App {
    const MODE_CLI = 'cli';
    const MODE_HTTP = 'http';

    public $host;
    public $path;
    public $mode;

    public $logPath = 'logs/';
    public $logErrors = true;

    public static function init() {
		
        set_include_path(get_include_path()
        . PATH_SEPARATOR . './modules'
        . PATH_SEPARATOR . './php-yaoi/modules'
        . PATH_SEPARATOR . './libraries'
        );
        spl_autoload_register(function($class){
            $path = explode('_', $class);
            $path2 = ($path ? implode('/', $path) . '/' : '') . $class . '.php';
            array_pop($path);
            $path = ($path ? implode('/', $path) . '/' : '') . $class . '.php';
            //die($path);

            if ($path = stream_resolve_include_path($path)) {
                require_once $path;
                return true;
            }
            elseif ($path2 = stream_resolve_include_path($path2)) {
                require_once $path2;
                return true;
            }
            else {
                return false;
            }
        });

        self::$instance = new static;

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

        require_once './conf/Main.php';

        self::$instance->setUpErrorHandling();

    }

    protected function setUpErrorHandling() {
        $app = $this;
        $errorLevels = array(
            E_ERROR => 'error',
            E_WARNING => 'warning',
            E_PARSE => 'parse',
            E_NOTICE => 'notice',
            E_CORE_ERROR => 'core-error',
            E_CORE_WARNING => 'core-warning',
            E_COMPILE_ERROR => 'compile-error',
            E_COMPILE_WARNING => 'compile-warning',
            E_USER_ERROR => 'user-error',
            E_USER_WARNING => 'user-warning',
            E_USER_NOTICE => 'user-notice',
            E_STRICT => 'strict',
            E_RECOVERABLE_ERROR => 'recoverable-error',
            E_DEPRECATED => 'deprecated',
            E_USER_DEPRECATED => 'user-deprecated',
            E_ALL => 'all',
        );


        $errorHandler = function($errno, $errstr, $errfile, $errline, $errcontext) use ($app, $errorLevels) {

            file_put_contents($app->logPath . 'php-errors-' . $errorLevels[$errno] . '.log',
                date('r') . "\t" . App::instance()->path
                . "\t" . $errno . "\t" . $errstr . "\t" . $errfile . ':' . $errline . "\t"
                . PHP_EOL
                //. Debug::backTrace(0, Debug::TRACE_TEXT)
                ,
                FILE_APPEND);

            if (E_RECOVERABLE_ERROR == $errno) {
                throw new Exception($errstr, $errno);
            }

        };

        register_shutdown_function(function() use ($errorHandler) {
            $error = error_get_last();
            if(null !== $error) {
                $errorHandler($error['type'], $error['message'], $error['file'], $error['line'], null);
            }
        });

        set_error_handler($errorHandler);
    }


    abstract function route($path = null, $host = null);


    protected static $instance;

    /**
     * @return static
     * @throws Exception
     */
    static function instance() {
        if (is_null(self::$instance)) {
            throw new Exception('Application is not initialized');
        }

        return self::$instance;
    }


    private static $resources = array();
    static function &db($id = 'default') {
        $resource = &self::$resources['db_' . $id];
        if (!isset($resource)) {
            $resource = Database_Client::createById($id);
        }
        return $resource;
    }

    static function cache($id = 'default') {
        return Storage::getInstance($id);
    }

    /**
     * @param string $id
     * @return Date_Source
     */
    static function time($id = 'default') {
        $resource = &self::$resources['time_' . $id];
        if (!isset($resource)) {
            $resource = new Date_Source();
        }
        return $resource;
    }


    /**
     * @param string $id
     * @return Log
     */
    static function log($id = 'default') {
        return Log::getInstance($id);
    }

}

