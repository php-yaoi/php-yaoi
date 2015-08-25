<?php
/**
 * Created by PhpStorm.
 * User: vpoturaev
 * Date: 8/25/15
 * Time: 16:38
 */

namespace Yaoi\Log\Driver;


use Yaoi\Console\Colored;
use Yaoi\Log;

class ColoredStdout extends Stdout
{
    /**
     * @param $message
     * @param string $type
     * @return $this
     */
    public function push($message, $type = Log::TYPE_MESSAGE)
    {
        if (is_object($message) && method_exists($message, '__toString')) {
            $message = (string)$message;
        }
        $message = $this->dsn->prefix . print_r($message, 1);
        switch ($type) {
            case Log::TYPE_ERROR:
                $message = Colored::get($message, Colored::FG_RED, null);
                break;
            case Log::TYPE_SUCCESS:
                $message = Colored::get($message, Colored::FG_GREEN, null);
                break;
        }
        echo $message, PHP_EOL;
        return $this;
    }

}