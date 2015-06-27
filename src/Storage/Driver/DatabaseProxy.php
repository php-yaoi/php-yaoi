<?php

namespace Yaoi\Storage\Driver;

use Yaoi\Sql\Symbol;
use Yaoi\Storage\Contract\Driver;
use Yaoi\Storage\Exception;
use Yaoi\Storage\Contract\Expire;
use Yaoi\App;
use Yaoi\BaseClass;
use Yaoi\Database;
use Yaoi\Date\TimeMachine;
use Yaoi\Migration;
use Yaoi\Storage\Settings;

class DatabaseProxy extends BaseClass implements Driver, Expire,
    \Yaoi\Migration\Needed
{
    private $dsn;
    /**
     * @var Database
     */
    private $db;

    /**
     * @var TimeMachine
     */
    private $time;

    /**
     * @var Symbol
     */
    private $table;
    /**
     * @var Symbol
     */
    private $keyField;
    /**
     * @var Symbol
     */
    private $valueField;
    /**
     * @var Symbol
     */
    private $expireField;

    public function __construct(Settings $dsn = null)
    {
        $this->dsn = $dsn ? $dsn : new Settings();
        if (empty($dsn->proxyClient)) {
            throw new Exception('proxyClient required in dsn', Exception::PROXY_REQUIRED);
        }
        $this->table = new Symbol($dsn->path
            ? $dsn->path : 'key_value_storage');
        $this->keyField = new Symbol('k');
        $this->valueField = new Symbol('v');
        $this->expireField = new Symbol('e');


        $this->db = Database::getInstance($this->dsn->proxyClient);
        $this->time = App::time($this->dsn->dateSource); // TODO use getInstance after Date_Source refactoring
    }

    public function set($key, $value, $ttl)
    {
        if (is_array($value) || is_object($value)) {
            throw new Exception('Complex data types not supported by this storage, consider serialization',
                Exception::SCALAR_REQUIRED);
        }

        if ($ttl && $ttl < 30 * 86400) {
            $ttl = $this->time->now() + $ttl;
        }

        if ($this->keyExists($key)) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $this->db
                ->update($this->table)
                ->set("? = ?", $this->valueField, $value)
                ->set('? = ?', $this->expireField, $ttl)
                ->where('? = ?', $this->keyField, $key)
                ->query();
        } else {
            $this->db->insert($this->table)->valuesRow(
                array(
                    $this->keyField->name => $key,
                    $this->valueField->name => $value,
                    $this->expireField->name => $ttl,
                ))
                ->query();
        }
    }

    public function get($key)
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $row = $this->db
            ->select($this->table)
            ->select('?, ?', $this->valueField, $this->expireField)
            ->where('? = ?', $this->keyField, $key)
            ->query()
            ->fetchRow();

        if (!$row) {
            return null;
        } else {
            $expire = $row[$this->expireField->name];
            if ($expire && $expire < $this->time->now()) {
                $this->delete($key);
                return null;
            }
            return $row[$this->valueField->name];
        }
    }

    public function keyExists($key)
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $row = $this->db
            ->select($this->table)
            ->select('?', $this->expireField)
            ->where('? = ?', $this->keyField, $key)
            ->query()
            ->fetchRow();

        if (!$row) {
            return false;
        } else {
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
        /** @noin2spection PhpMethodParametersCountMismatchInspection */
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
        $migrationId = 'storage_db_wrapper' . '_' . get_class($this->db->getDriver()) . $this->table->name;
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

        }, function() use ($table, $db) {
            $db->query("DROP TABLE ?", $table);
        });
    }
}