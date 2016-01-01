<?php

namespace Yaoi;

use Yaoi\Io\Request;

abstract class Router extends BaseClass
{
    protected $basePath = '/';

    public function setBasePath($basePath) {
        $this->basePath = $basePath;
        return $this;
    }

    abstract public function route(Request $request);

    public static function redirect($url, $permanent = false, $stop = true) {
        if ($permanent) {
            header('HTTP/1.1 301 Moved Permanently');
        }
        header('Location: ' . $url);
        if ($stop) {
            exit(); // todo proper stop
        }
    }
}