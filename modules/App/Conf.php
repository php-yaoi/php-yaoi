<?php
/**
 * Created by PhpStorm.
 * User: vpoturaev
 * Date: 6/18/15
 * Time: 12:11
 */
namespace Yaoi\App;

use Yaoi\App;

class Conf
{
    public static $dsn = array();

    public $modulesPath;
    public $yaoiPath;
    public $librariesPath;
    public $errorLogPath;

    protected function setUpErrorHandling()
    {
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


        $errorHandler = function ($errno, $errstr, $errfile, $errline, $errcontext) use ($app, $errorLevels) {

            file_put_contents($this->errorLogPath . 'php-errors-' . $errorLevels[$errno] . '.log',
                date('r') . "\t" . App::instance()->path
                . "\t" . $errno . "\t" . $errstr . "\t" . $errfile . ':' . $errline . "\t"
                . PHP_EOL
                //. Debug::backTrace(0, Debug::TRACE_TEXT)
                ,
                FILE_APPEND);

            if (E_RECOVERABLE_ERROR == $errno) {
                throw new \Exception($errstr, $errno);
            }

        };

        register_shutdown_function(function () use ($errorHandler) {
            $error = error_get_last();
            if (null !== $error) {
                $errorHandler($error['type'], $error['message'], $error['file'], $error['line'], null);
            }
        });

        set_error_handler($errorHandler);
    }

}