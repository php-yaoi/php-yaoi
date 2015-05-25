<?php

class Database_Driver_Sqlite extends Database_Driver {

    /**
     * @var SQLite3
     */
    private $dbHandle;

    private function connect() {
        if (null === $this->dbHandle) {
            $this->dbHandle = new SQLite3($this->dsn->path);
        }
    }


    public function query($statement)
    {
        if (null === $this->dbHandle) {
            $this->connect();
        }
        return @$this->dbHandle->query($statement);
    }

    public function lastInsertId()
    {
        return $this->dbHandle->lastInsertRowID();
    }

    public function rowsAffected($result)
    {
        return $this->dbHandle->changes();
    }

    public function escape($value)
    {
        if (null === $this->dbHandle) {
            $this->connect();
        }
        return $this->dbHandle->escapeString($value);
    }

    /**
     * @param SQLite3Result $result
     */
    public function rewind($result)
    {
        $result->reset();
    }

    /**
     * @param SQLite3Result $result
     * @return array|false
     */
    public function fetchAssoc($result)
    {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ? $row : null;
    }

    public function queryErrorMessage($result)
    {
        return $this->dbHandle->lastErrorCode() . ' ' . $this->dbHandle->lastErrorMsg();
    }

    public function disconnect()
    {
        if ($this->dbHandle) {
            $this->dbHandle->close();
        }
    }

    public function quoteSymbol(Sql_Symbol $symbol) {
        $result = '';
        foreach ($symbol->names as $name) {
            $result .= $name . '.';
        }
        if ($result) {
            $result = substr($result, 0, -1);
        }

        return $result;
    }

    public function getTableDefinition($tableName)
    {
        $definition = new Database_Definition_Table();
        $res = $this->query("PRAGMA table_info($tableName)");

        while ($r = $this->fetchAssoc($res)) {
            $field = $r['name'];
            if ($r['pk']) {
                $definition->primaryKey [$field]= $field;
            }
            $definition->defaults[$field] = $r['dflt_value'];
            $definition->notNull[$field] = (bool)$r['notnull'];
            $definition->columns[$field] = Database::COLUMN_TYPE_AUTO;
        }
        return $definition;
    }

    public function getLanguage()
    {
        return Database::LANG_SQLITE;
    }


}