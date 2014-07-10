<?php

class Storage_Driver_Http implements Storage_Driver {
    /**
     * @var Storage_Dsn
     */
    private $dsn;

    private $dsnUrl;

    /**
     * @var Http_Client
     */
    private $http;

    public function __construct(Storage_Dsn $dsn = null)
    {
        $this->dsn = $dsn;
        $this->dsnUrl = (string)$dsn;
        $this->http = new Http_Client();
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