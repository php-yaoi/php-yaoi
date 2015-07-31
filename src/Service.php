<?php
namespace Yaoi;

use Closure;
use Yaoi\Service\Exception;
use Yaoi\String\Utils;
use Yaoi\Service\Settings;

/**
 * Class Service
 * @package Yaoi
 */

abstract class Service extends BaseClass
{
    const PRIMARY = 'primary';
    const FALLBACK = 'fallback';

    private static $registry = array();
    public static function register($settings, $identifier = self::PRIMARY) {
        $class = get_called_class();
        self::$registry[$class][$identifier] = $settings;
    }

    private static $instances = array();

    public function getSettings() {
        return $this->settings;
    }

    /**
     * @param null $dsnString
     * @return Settings
     */
    public static function createSettings($dsnString = null) {
        $className = static::getSettingsClassName();
        return new $className($dsnString);
    }


    private static function resolveClosure(Closure $closure) {
        $result = $closure();
        if ($result instanceof Closure) {
            return self::resolveClosure($result);
        }
        else {
            return $result;
        }
    }

    /**
     * @param Settings|string|Closure|null $settings
     * @throws Service\Exception
     */
    public function __construct($settings = null)
    {
        $settingsClass = static::getSettingsClassName();
        if (null === $settingsClass) {
            $settingsClass = Settings::className();
        }

        if (null !== $settings) {
            if ($settings instanceof Closure) {
                $settings = self::resolveClosure($settings);
            }

            if (is_string($settings)) {
                $this->settings = new $settingsClass($settings);
            }
            elseif ($settings instanceof $settingsClass) {
                $this->settings = $settings;
            }
            else {
                throw new Service\Exception('Invalid argument. ' . Debug::varBrief($settings), Service\Exception::SETTINGS_REQUIRED);
            }
        } else {
            $this->settings = new $settingsClass();
        }
    }



    private static function findOrCreateInstance($serviceClassName, $identifier, $idIsSettings = false) {
        //var_dump('=-=-=-=-=-=-=-=-=-=-=-=-=-=',$serviceClassName, $identifier, $idIsSettings);

        if ($identifier instanceof Closure) {
            $identifier = self::resolveClosure($identifier);
            if (null === $identifier) {
                throw new Exception('Null closure result. Did you forget to return value at settings closure?',
                    Exception::INVALID_ARGUMENT);
            }
        }

        if (null === $identifier) {
            if ($idIsSettings) {
                return new $serviceClassName();
            }
            else {
                $identifier = self::PRIMARY;
            }
        }

        if (is_string($identifier)) {
            $registry = isset(self::$registry[$serviceClassName]) ? self::$registry[$serviceClassName] : array();

            if ($idIsSettings) {

                if ($registry && array_key_exists($identifier, $registry)) {
                    //var_dump('goin under -=-=-=-=-=-=-=-', $identifier);
                    return self::findOrCreateInstance($serviceClassName, $identifier);
                }

                return new $serviceClassName($identifier);
            }


            $resource = &self::$instances[$serviceClassName][$identifier];

            if (null !== $resource) {
                return $resource;
            }

            if ($registry) {
                if (array_key_exists($identifier, self::$registry[$serviceClassName])) {
                    $settings = self::$registry[$serviceClassName][$identifier];
                    //var_dump('goin under zero -=-=-=-=-=-=-=-', $settings);

                    $resource = self::findOrCreateInstance($serviceClassName, $settings, true);
                    return $resource;
                }
                else {
                    if (array_key_exists(self::FALLBACK, $registry)) {
                        $resource = self::findOrCreateInstance($serviceClassName, self::FALLBACK);
                    }
                }
            }

            if (null === $resource) {
                throw new Service\Exception('Service ' . $serviceClassName . ' not configured for "' . $identifier . '", fallback missing',
                    Service\Exception::NO_FALLBACK);
            }
            else {
                return $resource;
            }
        }

        if ($identifier instanceof Service) {
            return $identifier;
        }

        if ($identifier instanceof Settings) {
            return new $serviceClassName($identifier);
        }



        throw new Exception('Invalid argument', Exception::INVALID_ARGUMENT);
    }

    /**
     * Returns client instance
     *
     *
     * @param string|Service|Settings|Closure $identifier
     * @return static
     * @throws Service\Exception
     * fallback instead default
     */
    public static function getInstance($identifier = self::PRIMARY)
    {
        $serviceClassName = get_called_class();


        return self::findOrCreateInstance($serviceClassName, $identifier);
    }


    /**
     * @var null|Settings
     */
    protected $settings;

    protected $driver;

    /**
     * @return object
     * @throws Service\Exception
     */
    public function getDriver()
    {
        if (null === $this->driver) {
            if ($this->settings && $this->settings->driverClassName) {
                $driverClass = $this->settings->driverClassName;
            } else {
                $scheme = $this->settings->scheme;
                $scheme = explode('.', $scheme, 2);
                if (2 === count($scheme)) {
                    $driverClass = '\\' . Utils::toCamelCase($scheme[0], '-') . '\\'
                        . get_called_class() . '\Driver\\' . Utils::toCamelCase($scheme[1], '-');
                } else {
                    $driverClass = get_called_class() . '\Driver\\' . Utils::toCamelCase($scheme[0], '-');
                }

            }
            if (!class_exists($driverClass)) {
                throw new Service\Exception($driverClass . ' (' . $this->settings->scheme . ') not found', Service\Exception::NO_DRIVER);
            }
            $this->driver = new $driverClass($this->settings);
        }

        /*
        if ($this->driver instanceof Migration_Required) {
            Migration_Manager::getInstance()->perform($this->driver->getMigration());
        }
        */

        return $this->driver;
    }

    /**
     * @return string
     */
    protected static function getSettingsClassName()
    {
        return Settings::className();
    }
}