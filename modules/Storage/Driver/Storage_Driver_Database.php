<?php

class Storage_Driver_Database extends Base_Class implements Storage_Driver, Storage_ArrayKey {
    private $dsn;
    /**
     * @var Database
     */
    private $db;

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

    /**
     * @var Storage_Dsn
     */
    public function __construct(Storage_Dsn $dsn = null)
    {
        $this->dsn = $dsn;
        if (empty($dsn->proxyClient)) {
            throw new Client_Exception('proxyClient required in dsn', Client_Exception::DSN_REQUIRED);
        }
        $dsn->path;

        $this->db = Database::getInstance($this->dsn->proxyClient);
    }

    public function set($key, $value, $ttl) {
        if ($this->keyExists($key)) {
            $this->db->update($this->table)->set("? = ?", $this->keyField, $value);
        }
        else {
            $this->db->insert($this->table)->valuesRow(array($this->keyField->name => $value));
        }
    }

    public function get($key) {
        if (is_array($key)) {
            $key = implode('/', $key);
        }

        $row = $this->db
            ->select($this->table)
            ->select('?', new Sql_Symbol($this->valueField))
            ->where('? = ?', new Sql_Symbol($this->keyField), $key)
            ->query()
            ->fetchRow();

        if (!$row) {
            return null;
        }
        else {
            return $row[$this->valueField];
        }
    }

    public function keyExists($key) {
        if (is_array($key)) {
            $key = implode('/', $key);
        }

        $row = $this->db
            ->select($this->table)
            ->select('?, ?', new Sql_Symbol($this->valueField), new Sql_Symbol($this->expireField))
            ->where('? = ?', new Sql_Symbol($this->keyField), $key)
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
            ->where('? = ?', new Sql_Symbol($this->keyField), $key)
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

}