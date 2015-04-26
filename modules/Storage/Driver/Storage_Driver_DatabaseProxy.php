<?php

class Storage_Driver_DatabaseProxy extends Base_Class implements Storage_Driver, Storage_Expire, Migration_Required {
    private $dsn;
    /**
     * @var Database
     */
    private $db;

    /**
     * @var Date_Source
     */
    private $time;

    /**
     * @var Sql_Symbol
     */
    private $table;
    /**
     * @var Sql_Symbol
     */
    private $keyField;
    /**
     * @var Sql_Symbol
     */
    private $valueField;
    /**
     * @var Sql_Symbol
     */
    private $expireField;

    public function __construct(Storage_Dsn $dsn = null)
    {
        $this->dsn = $dsn ? $dsn : new Storage_Dsn();
        if (empty($dsn->proxyClient)) {
            throw new Storage_Exception('proxyClient required in dsn', Storage_Exception::PROXY_REQUIRED);
        }
        $this->table = new Sql_Symbol($dsn->path
            ? $dsn->path :
            'key_value_storage');
        $this->keyField = new Sql_Symbol('k');
        $this->valueField = new Sql_Symbol('v');
        $this->expireField = new Sql_Symbol('e');


        $this->db = Database::getInstance($this->dsn->proxyClient);
        $this->time = Yaoi::time($this->dsn->dateSource); // TODO use getInstance after Date_Source refactoring
    }

    public function set($key, $value, $ttl) {
        if (is_array($value) || is_object($value)) {
            throw new Storage_Exception('Complex data types not supported by this storage, consider serialization',
                Storage_Exception::SCALAR_REQUIRED);
        }

        if ($ttl && $ttl < 30*86400) {
            $ttl = $this->time->now() + $ttl;
        }

        if ($this->keyExists($key)) {
            $this->db
                ->update($this->table)
                ->set("? = ?", $this->valueField, $value)
                ->set('? = ?', $this->expireField, $ttl)
                ->where('? = ?', $this->keyField, $key)
                ->query();
        }
        else {
            $this->db->insert($this->table)->valuesRow(
                array(
                    $this->keyField->name => $key,
                    $this->valueField->name => $value,
                    $this->expireField->name => $ttl,
                    ))
                ->query();
        }
    }

    public function get($key) {
        $row = $this->db
            ->select($this->table)
            ->select('?, ?', $this->valueField, $this->expireField)
            ->where('? = ?', $this->keyField, $key)
            ->query()
            ->fetchRow();

        if (!$row) {
            return null;
        }
        else {
            $expire = $row[$this->expireField->name];
            if ($expire && $expire < $this->time->now()) {
                $this->delete($key);
                return null;
            }
            return $row[$this->valueField->name];
        }
    }

    public function keyExists($key) {
        $row = $this->db
            ->select($this->table)
            ->select('?', $this->expireField)
            ->where('? = ?', $this->keyField, $key)
            ->query()
            ->fetchRow();

        if (!$row) {
            return false;
        }
        else {
            $expireUt = $row[$this->expireField->name];
            if ($expireUt && $this->time->now() > $expireUt) {
                $this->delete($key);
                return false;
            }
            return true;
        }
    }

    public function delete($key)
    {
        $this->db
            ->delete($this->table)
            ->where('? = ?', $this->keyField, $key)
            ->query()
            ->execute();
    }

    public function deleteAll()
    {
        $this->db
            ->delete($this->table)
            ->query()
            ->execute();
    }

    /**
     * @return Migration
     */
    public function getMigration()
    {
        $migrationId = 'storage_db_wrapper'  . '_' . get_class($this->db->getDriver()) . $this->table->name;
        $table = $this->table;
        $keyField = $this->keyField;
        $valueField = $this->valueField;
        $expireField = $this->expireField;
        $db = $this->db;
        return new Migration($migrationId, function() use ($table, $keyField, $valueField, $expireField, $db) {
            //if ($this->db->getDriver() instanceof Database_Server_Mysql) {
                $db->query("CREATE TABLE IF NOT EXISTS :table (
:key VARCHAR(255) NOT NULL DEFAULT '',
:value TEXT NOT NULL,
:expire INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (:key)
)",
                    array(
                        'table' => $table,
                        'key' => $keyField,
                        'value' => $valueField,
                        'expire' => $expireField,
                        ));
            //}

        }, function(){
            $this->db->query("DROP TABLE ?", $this->table);
        });
    }
}