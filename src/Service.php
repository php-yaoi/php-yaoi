<?php
namespace Yaoi;

use Yaoi\Service\Contract;
use Closure;
use Yaoi\Database\Driver;
use Yaoi\Service\Exception;
use Yaoi\String\Utils;
use Yaoi\Service\Settings;

/**
 * Class Service
 * @package Yaoi
 */

abstract class Service extends BaseClass implements Contract
{
    const PRIMARY = 'default';
    const FALLBACK = 'fallback';

    private static $registry = array();
    public static function register($identifier, $settings) {
        $class = get_called_class();
        self::$registry[$class][$identifier] = $settings;
    }

    private static $instances = array();

    public function getSettings() {
        return $this->settings;
    }

    /**
     * @return Settings
     */
    public static function createSettings() {
        $className = static::getSettingsClassName();
        return new $className;
    }

    /**
     * @param null $settings
     * @return null|Service\Settings
     * @throws Service\Exception
     */
    public static function settings($settings = null)
    {
        $settingsClassName = static::getSettingsClassName();

        if ($settings instanceof $settingsClassName) {
            return $settings;
        }

        if ($settings instanceof Closure) {
            $settings = $settings();
            if (!$settings instanceof $settingsClassName) {
                throw new Service\Exception('Closure should return ' . $settingsClassName . ' instance',
                    Service\Exception::SETTINGS_REQUIRED);
            }
            return $settings;
        }

        if (is_string($settings)) {
            $settings = new $settingsClassName($settings);
            return $settings;
        }

        throw new Service\Exception('Invalid argument', Service\Exception::SETTINGS_REQUIRED);
    }


    /**
     * @param Settings|string|Closure|null $settings
     * @throws Service\Exception
     */
    public function __construct($settings = null)
    {
        if (null !== $settings) {
            $this->settings = static::settings($settings);
        }
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

        if (is_string($identifier)) {
            $resource = &self::$instances[$serviceClassName][$identifier];

            if (null !== $resource) {
                return $resource;
            }

            if (!isset(self::$registry[$serviceClassName][$identifier])) {
                if (self::FALLBACK === $identifier) {
                    throw new Service\Exception('Service not configured, fallback missing',
                        Service\Exception::NO_FALLBACK);
                }

                $resource = static::getInstance(self::FALLBACK);
                return $resource;
            } else {
                $settings = self::$registry[$serviceClassName][$identifier];

                if (is_string($settings) && isset(self::$registry[$serviceClassName][$settings])) {
                    $resource = static::getInstance($settings);
                    return $resource;
                }

                $resource = new static($settings);
                if (!$resource->settings) {
                    var_dump($resource, $identifier);
                    die('!!!');
                }
                $resource->settings->identifier = $identifier;
                return $resource;
            }
        }

        if ($identifier instanceof Service) {
            return $identifier;
        }

        if ($identifier instanceof Settings) {
            return new static($identifier);
        }

        throw new Exception('String identifier required', Exception::INVALID_ARGUMENT); // TODO change message here
    }


    /**
     * @var null|Settings
     */
    protected $settings;

    protected $driver;

    /**
     * @return Driver
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
                }
                else {
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

}