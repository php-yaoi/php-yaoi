<?php

class Storage_Driver_Database extends Base_Class implements Storage_Driver, Storage_ArrayKey, Migration_Required {
    private $dsn;
    /**
     * @var Database
     */
    private $db;

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
        $this->dsn = $dsn;
        if (empty($dsn->proxyClient)) {
            throw new Client_Exception('proxyClient required in dsn', Client_Exception::DSN_REQUIRED);
        }
        $this->table = new Sql_Symbol($dsn->path);
        $this->keyField = new Sql_Symbol('k');
        $this->valueField = new Sql_Symbol('v');
        $this->expireField = new Sql_Symbol('e');


        $this->db = Database::getInstance($this->dsn->proxyClient);
    }

    public function set($key, $value, $ttl) {
        echo 'hooy';
        if ($this->keyExists($key)) {
            echo 'hooo';
            $this->db
                ->update($this->table)
                ->set("? = ?", $this->valueField, $value)
                ->where('? = ?', $this->keyField, $key)
                ->query();
        }
        else {
            echo 'h999';
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
        if (is_array($key)) {
            $key = implode('/', $key);
        }

        $row = $this->db
            ->select($this->table)
            ->select('?', $this->valueField)
            ->where('? = ?', $this->keyField, $key)
            ->query()
            ->fetchRow();

        if (!$row) {
            return null;
        }
        else {
            return $row[$this->valueField->name];
        }
    }

    public function keyExists($key) {
        if (is_array($key)) {
            $key = implode('/', $key);
        }

        $row = $this->db
            ->select($this->table)
            ->select('?, ?', $this->valueField, $this->expireField)
            ->where('? = ?', $this->keyField, $key)
            ->query()
            ->fetchRow();

        if (!$row) {
            return false;
        }
        else {
            return true;
        }
    }

    public function delete($key)
    {
        if (is_array($key)) {
            $key = implode('/', $key);
        }

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
        $migrationId = 'storage_db_wrapper'  . '_' . $this->table->name;
        return new Migration($migrationId, function() {
            //if ($this->db->getDriver() instanceof Database_Server_Mysql) {
                $this->db->query("CREATE TABLE :table (
:key VARCHAR(255) NOT NULL DEFAULT '',
:value TEXT NOT NULL DEFAULT '',
:expire INTEGER NOT NULL DEFAULT 0,
PRIMARY KEY (:key)
)",
                    array(
                        'table' => $this->table,
                        'key' => $this->keyField,
                        'value' => $this->valueField,
                        'expire' => $this->expireField,
                        ));
            //}

        });
    }
}