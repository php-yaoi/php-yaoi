<?php

namespace Yaoi\Storage\Driver;

use Yaoi\Http\Client;
use Yaoi\Storage\Contract\Driver;
use Yaoi\Storage\Settings;

class Http implements Driver
{
    /**
     * @var Settings
     */
    private $dsn;

    private $dsnUrl;

    /**
     * @var Client
     */
    private $http;

    public function __construct(Settings $dsn = null)
    {
        $this->dsn = $dsn;
        $this->dsnUrl = (string)$dsn;
        $this->http = new Client();
    }

    public function get($key)
    {
        $result = $this->http->reset()->fetch($this->dsnUrl . $key);
        return $result;
    }

    public function keyExists($key)
    {
        $result = $this->http->reset()->fetch($this->dsnUrl . $key);
        return (bool)$result;
    }

    public function set($key, $value, $ttl)
    {
        $this->http->reset()->post = array(
            'cmd' => 'set',
            'value' => $value,
            'ttl' => $ttl
        );
        $this->http->fetch($this->dsnUrl . $key);
    }

    public function delete($key)
    {
        $this->http->reset()->post = array(
            'cmd' => 'delete',
        );
        $this->http->fetch($this->dsnUrl . $key);
    }

    public function deleteAll()
    {
        $this->http->reset()->post = array(
            'deleteAll'
        );
        $this->http->fetch($this->dsnUrl);
    }

}