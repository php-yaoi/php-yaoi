<?php

abstract class Abstract_App {
    public $host;
    public $path;

    public static function run() {
		
        set_include_path(get_include_path()
        . PATH_SEPARATOR . './modules'
        . PATH_SEPARATOR . './php-yaoi/modules');
        spl_autoload_register(function($class){
            $path = explode('_', $class);
            array_pop($path);
            $path = ($path ? implode('/', $path) . '/' : '') . $class . '.php';
            //die($path);

            if ($path = stream_resolve_include_path($path)) {
                require_once $path;
                return true;
            }
            else {
                return false;
            }
        });

        require_once './conf/Main.php';

        self::$instance = new static;

        // TODO properly detect CLI mode and show "Usage" message by default

        if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
            self::$instance->path = $_SERVER['REQUEST_URI'];
            self::$instance->host = $_SERVER['HTTP_HOST'];
        }
        elseif (isset($_SERVER['argv'][1])) {
            self::$instance->path = $_SERVER['argv'][1];
            if (isset($_SERVER['argv'][2])) {
                self::$instance->host = $_SERVER['argv'][2];
            }
        }

        self::$instance->route(self::$instance->path, self::$instance->host);
    }

    abstract function route($path = null, $host = null);


    protected static $instance;
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
        $resource = &self::$resources['st_' . $id];
        if (!isset($resource)) {
            if (isset(Storage_Conf::$dsn[$id])) {
                $resource = new Storage_Client(Storage_Conf::$dsn[$id]);
            }
            elseif ('default' == $id) {
                throw new Storage_Exception('Default storage connection not configured', Storage_Exception::DEFAULT_NOT_SET);
            }
            else {
                $resource = static::cache();
            }
        }
        return $resource;
    }
}

