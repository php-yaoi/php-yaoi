<?php

class String_Dsn extends Base_Class {
    public $scheme;
    public $username;
    public $password;
    public $hostname;
    public $port;
    public $path;

    public function __construct($dsnUrl) {
        if (false === strpos($dsnUrl, '://') ||  !$p = parse_url($dsnUrl)) {
            throw new String_Exception('Malformed DSN URL', String_Exception::BAD_DSN);
        }

        if (isset($p['query'])) {
            parse_str($p['query'],$p['query']);
            foreach ($p['query'] as $key => $value) {
                $this->$key = $value;
            }
        }

        if (isset($p['scheme'])) {
            $this->scheme = $p['scheme'];
        }

        if (isset($p['path'])) {
            $this->path = substr($p['path'], 1);
        }

        if (isset($p['user'])) {
            $this->username = $p['user'];
        }

        if (isset($p['pass'])) {
            $this->password = $p['pass'];
        }

        if (isset($p['host'])) {
            $this->hostname = $p['host'];
        }

        if (isset($p['port'])) {
            $this->port = $p['port'];
        }
    }
}