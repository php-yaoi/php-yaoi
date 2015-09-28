<?php

namespace Yaoi\String;

use Yaoi\BaseClass;

class Dsn extends BaseClass
{
    private $dsnUrl;
    private $schemeDelimiterPos;

    public $scheme;
    public $username;
    public $password;
    public $hostname;
    public $port;
    public $path;


    private function preUrlEncode() {
        if (strpos($this->dsnUrl, '\\') !== false) {
            $this->dsnUrl = strtr($this->dsnUrl, array(
                '\@' => '%40',
                '\/' => '%2F',
                '\:' => '%3A',
                '\?' => '%3F',
                '\&' => '%26',
            ));
        }
    }

    private function checkUserPasswordSchema() {
        if (false === $this->schemeDelimiterPos) {
            if (false === strpos($this->dsnUrl, ':')) {
                $this->scheme = urldecode($this->dsnUrl);
            }
            else {
                $data = explode(':', $this->dsnUrl, 2);
                $this->username = urldecode($data[0]);
                $this->password = urldecode($data[1]);
            }
            return true;
        }

        return false;
    }

    private function checkHostlessSchema() {
        if (strlen($this->dsnUrl) === $this->schemeDelimiterPos + 3) {
            $this->scheme = substr($this->dsnUrl, 0, $this->schemeDelimiterPos);
            return true;
        }

        return false;
    }

    private static $map = array(
        'scheme' => 'scheme',
        'username' => 'user',
        'password' => 'pass',
        'hostname' => 'host',
        'path' => 'path',
        'port' => 'port'
    );


    private function prepareParseUrl() {
        if ('/' === $this->dsnUrl[$this->schemeDelimiterPos + 3]) {
            $this->dsnUrl = substr($this->dsnUrl, 0, $this->schemeDelimiterPos) . '://dummy'
                . substr($this->dsnUrl, $this->schemeDelimiterPos + 3);
            $p = parse_url($this->dsnUrl);
            $p['host'] = null;
        }
        else {
            $p = parse_url($this->dsnUrl);
        }

        if (!$p) {
            throw new Exception('Malformed DSN URL', Exception::BAD_DSN);
        }
        return $p;
    }

    private function parseUrl() {
        $p = $this->prepareParseUrl();

        if (isset($p['query'])) {
            parse_str($p['query'], $parsed);
            if (null !== $parsed) {
                foreach ($parsed as $key => $value) {
                    $this->$key = $value;
                }
            }
        }
        unset($p['query']);

        foreach (self::$map as $field => $key) {
            if (isset($p[$key])) {
                $this->$field = urldecode($p[$key]);
            }
        }


        $this->path = null === $this->path ? null : substr($this->path, 1);
        $this->port = null === $this->port ? null : (int)$p['port'];
    }


    /**
     * @param null $dsnUrl
     * @throws Exception
     */
    public function __construct($dsnUrl = null)
    {
        if (null === $dsnUrl) {
            return;
        }

        do {
            $this->dsnUrl = $dsnUrl;

            $this->preUrlEncode();
            $this->schemeDelimiterPos = strpos($this->dsnUrl, '://');
            if ($this->checkUserPasswordSchema()) {
                break;
            }

            /*
             * hostless schema like test:///path?333 or test:// or
             */
            if ($this->checkHostlessSchema()) {
                break;
            }

            $this->parseUrl();
        }
        while (false);
        unset($this->dsnUrl);
        unset($this->schemeDelimiterPos);
    }


    public function __toString()
    {
        // http://user:password@host:port/path?query
        $result = $this->scheme;

        $h = $this->hostname;
        if ($h) {
            if ($this->port) {
                $h .= ':' . $this->port;
            }
            if ($this->username) {
                $h = '@' . $h;
                if ($this->password) {
                    $h = ':' . $this->password . $h;
                }
                $h = $this->username . $h;
            }
        }


        $result .= '://' . $h . '/' . $this->path;

        $params = (array)$this;
        unset($params['scheme']);
        unset($params['username']);
        unset($params['password']);
        unset($params['hostname']);
        unset($params['port']);
        unset($params['path']);

        if ($params) {
            $queryString = http_build_query($params);
            $result .= '?' . $queryString;
        }

        return $result;
    }

}