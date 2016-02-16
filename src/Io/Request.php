<?php

namespace Yaoi\Io;

use Yaoi\BaseClass;
use Yaoi\Io\Request\Server;
use Yaoi\String\Parser;
use Yaoi\String\StringValue;

class Request extends BaseClass
{
    protected $data;

    /** @var  Server  */
    protected $server;

    const GET = 'get';
    const POST = 'post';
    const REQUEST = 'request';
    const ARGV = 'argv';
    const COOKIE = 'cookie';
    const SERVER = 'server';

    public function get($param, $default = null) {
        return $this->param(self::GET, $param, $default);
    }

    public function post($param, $default = null) {
        return $this->param(self::POST, $param, $default);
    }

    public function argv($param, $default = null) {
        $argv = $this->server->argv;
        return isset($argv[$param]) ? $argv[$param] : $default;
    }

    public function cookie($param, $default) {
        return $this->param(self::COOKIE, $param, $default);
    }

    public function request($param, $default = null) {
        return $this->param(self::REQUEST, $param, $default);
    }

    /**
     * @return Server
     */
    public function server() {
        return $this->server;
    }

    private function param($type, $param, $default) {
        return isset($this->data[$type][$param])
            ? $this->data[$type][$param]
            : $default;
    }

    public function hostname() {
        return $this->server->HTTP_HOST;
    }

    private $path;
    public function path() {
        if (null === $this->path) {
            $this->path = $this->server->REQUEST_URI;
            if (false !== $position = strpos($this->path, '?')) {
                $this->path = substr($this->path, 0, $position);
            }
        }
        return $this->path;
    }

    protected $isCli;

    public function isCli() {
        return $this->isCli;
    }

    public function __construct() {
        $this->server = new Server;
    }

    public static function createAuto() {
        $request = new static();
        $request->data = array(
            self::GET => $_GET,
            self::POST => $_POST,
            self::REQUEST => $_REQUEST,
            self::COOKIE => $_COOKIE,
        );
        $request->server = Server::fromArray($_SERVER);
        $request->isCli = PHP_SAPI === 'cli';
        return $request;
    }
}