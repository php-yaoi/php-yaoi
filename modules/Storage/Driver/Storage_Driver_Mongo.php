<?php

class Storage_Driver_Mongo implements Storage_Driver {
    /**
     * @var MongoClient
     */
    private $mongo;

    /**
     * @var MongoDB
     */
    private $db;

    /**
     * @var MongoCollection
     */
    private $collection;

    public function __construct(Storage_Dsn $dsn = null) {
        $host = $dsn->hostname ? $dsn->hostname : 'localhost';
        $port = $dsn->port ? $dsn->port : 27017;

        $dbCollection = 'storage';
        $dbName = 'test';
        if ($dsn->path) {
            $path = explode('/', $dsn->path, 2);
            $dbName = $path[0];
            if (isset($path[1])) {
                $dbCollection = $path[1];
            }
        }

        $server = 'mongodb://' . $host . ':' . $port;
        $this->mongo = new MongoClient($server);
        $this->db = $this->mongo->$dbName;
        $this->collection = $this->db->$dbCollection;
        $this->collection->ensureIndex(array('k' => 1));
    }

    public function get($key)
    {
        if ($res = $this->collection->findOne(array('k' => $key))) {
            if ($res['t'] && $res['t'] < App::time()->now()) {
                $this->delete($key);
                return null;
            }
            return $res['v'];
        }
        return null;
    }

    public function keyExists($key)
    {
        if ($res = $this->collection->findOne(array('k' => $key))) {
            return true;
        }
        return false;
    }

    public function set($key, $value, $ttl)
    {
        $this->collection->insert(array('k' => $key, 'v' => $value, 't' => $ttl ? Yaoi::time()->now() + $ttl : null));
    }

    public function delete($key)
    {
        $this->collection->remove(array('k' => $key));
    }

    public function deleteAll()
    {
        $this->collection->remove(array());
    }

} 