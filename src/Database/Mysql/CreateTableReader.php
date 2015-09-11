<?php

namespace Yaoi\Database\Mysql;

use Yaoi\Database;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;
use Yaoi\String\Parser;
use Yaoi\String\Tokenizer;
use Yaoi\String\Utils;

class CreateTableReader
{
    private $statement;
    private $database;
    public function __construct($statement, Database\Contract $database) {
        $this->database = $database;
        $this->statement = $statement;
    }

    private $tokens;
    private $binds = array();
    private $deQuoted = '';
    private $deBracketed = '';
    private $bindIndex = 0;
    private $tableName;
    /** @var  \stdClass */
    private $columns;
    private $indexes = array();
    private $foreignKeys = array();
    /** @var  Table */
    private $table;

    /** @var  Parser */
    private $tail;

    private function tokenize() {
        $tokenizer = Utility::create()->getStatementTokenizer();
        $this->tokens = $tokenizer->tokenize($this->statement);

        foreach ($this->tokens as $index => $token) {
            if (is_array($token)) {
                if ($token[1] == '#' || $token[1] == '-- ' || $token[1] === '/*') {
                    unset($this->tokens[$index]);
                }
                else {
                    $this->binds ['?' . $this->bindIndex . '?']= $token[0];
                    $this->deQuoted .= '?' . $this->bindIndex . '?';
                    ++$this->bindIndex;
                }

            }
            else {
                $this->deQuoted .= $token;
            }
        }

        $this->deQuoted = preg_replace('/\s+/', ' ', $this->deQuoted);
        //echo PHP_EOL, $this->deQuoted;
    }
    private function bracketize() {
        $parser = new Parser($this->deQuoted);
        $this->tableName = $this->unbind(trim($parser->inner('CREATE TABLE ', '(')));

        $lines = $parser->inner(null, ')', true);

        $bracketTokenizer = new Tokenizer();
        $bracketTokenizer->addQuote('(', ')');
        $bracketTokens = $bracketTokenizer->tokenize($lines);

        $this->deBracketed = '';
        foreach ($bracketTokens as $token) {
            if (is_string($token)) {
                $this->deBracketed .= $token;
            }
            else {
                $this->binds ['?' . $this->bindIndex . '?']= $token[0];
                $this->deBracketed .= '(?' . $this->bindIndex . '?)';
                ++$this->bindIndex;
            }
        }


        $this->tail = $parser->inner();
        //echo PHP_EOL, 'deBracketed:', PHP_EOL, $this->deBracketed;
    }
    
    private function unbind($value) {
        if (isset($this->binds[trim($value)])) {
            return $this->binds[trim($value)];
        }
        else {
            return $value;
        }
    }

    private function parseColumn($line) {
        $parser = new Parser($line);
        $columnName = trim($parser->inner(null, ' '), '`');
        if (isset($this->binds[$columnName])) {
            $columnName = $this->binds[$columnName];
        }
        $type = $parser->inner(null, ' ');
        $notNull = strpos($line, 'NOT NULL') !== false;
        $autoId = strpos($line, 'AUTO_INCREMENT') !== false;
        $default = $parser->inner('DEFAULT ');
        if (!$default->isEmpty()) {
            $default = (string)$default;
            if ('NULL' === $default) {
                $default = null;
            }
            elseif (strpos($default, '?') !== false) {
                $default = strtr($default, $this->binds);
            }

        }
        else {
            $default = false;
        }

        $type = strtr($type, $this->binds);
        $flags = $this->getTypeByString($type);

        if ($notNull) {
            $flags += Column::NOT_NULL;
        }

        if ($autoId) {
            $flags += Column::AUTO_ID;
        }

        $column = new Column($flags);

        if ($length = (string)$parser->setOffset(0)->inner('VARCHAR(', ')')) {
            $column->setStringLength($this->binds[$length], false);
        }
        elseif ($length = (string)$parser->setOffset(0)->inner('CHAR(', ')')) {
            $column->setStringLength($this->binds[$length], true);
        }


        $column->setDefault($default);

        $column->schemaName = $columnName;

        //var_dump($columnName);
        $this->columns->$columnName = $column;
    }

    private function parseLines() {
        $lines = explode(',', $this->deBracketed);

        $this->columns = new \stdClass();
        $this->indexes = array();
        $this->foreignKeys = array();

        foreach ($lines as $line) {
            $line = trim($line);
            $parser = new Parser($line);

            if ($parser->starts('PRIMARY KEY')) {
                $indexColumns = trim($parser->inner('(', ')'));
                $this->indexes []= array(Index::TYPE_PRIMARY, Index::TYPE_PRIMARY, $indexColumns);
            }
            elseif ($parser->starts('UNIQUE KEY')) {
                $indexName = trim($parser->inner('KEY', '('), '`');
                $indexColumns = trim($parser->inner(null, ')'));
                $this->indexes []= array(Index::TYPE_UNIQUE, $indexName, $indexColumns);
            }
            elseif ($parser->starts('KEY')) {
                $indexName = trim($parser->inner('KEY', '('), '`');
                $indexColumns = trim($parser->inner(null, ')'));
                $this->indexes []= array(Index::TYPE_KEY, $indexName, $indexColumns);
            }
            elseif ($parser->starts('CONSTRAINT')) {
                $this->parseConstraint($parser);
            }
            else {
                $this->parseColumn($line);
            }

        }
    }


    private function parseConstraint(Parser $parser) {
        $indexName = trim($parser->inner('CONSTRAINT', 'FOREIGN KEY'), '`');
        $indexColumns = trim($parser->inner('(', ')'));
        $referenceName = trim($parser->inner('REFERENCES', '('), '`');
        $referenceColumns = trim($parser->inner(null, ')'));
        $extra = $parser->inner();
        $ons = explode('ON ', $extra);
        $onUpdate = $onDelete = null;
        if (count($ons) > 1) {
            unset($ons[0]);
            //var_dump($ons);
            foreach ($ons as $on) {
                $on = trim(strtoupper($on));
                $on = explode(' ', $on, 2);
                if ($on[0] === 'UPDATE') {
                    $onUpdate = trim($on[1]);
                }
                elseif ($on[0] === 'DELETE') {
                    $onDelete = trim($on[1]);
                }
            }
        }
        $this->foreignKeys []= array($indexName, $indexColumns, $referenceName, $referenceColumns, $onUpdate, $onDelete);
    }

    private function buildIndexes() {
        foreach ($this->indexes as $indexData) {
            $type = $indexData[0];
            $name = trim($indexData[1]);
            if (isset($this->binds[$name])) {
                $name = $this->binds[$name];
            }
            $indexColumns = array();
            if (isset($this->binds[$indexData[2]])) {
                $indexData[2] = $this->binds[$indexData[2]];
            }
            foreach (explode(',',$indexData[2]) as $columnName) {
                $columnName = trim($columnName);
                //var_dump($columnName);

                if (isset($this->binds[$columnName])) {
                    $columnName = $this->binds[$columnName];
                }
                if (isset($this->binds[$columnName])) {
                    $columnName = $this->binds[$columnName];
                }

                //var_dump($columnName);
                $indexColumns []= $this->columns->$columnName;
            }
            $index = new Index($indexColumns);
            $index->setName($name);
            $index->setType($type);
            $this->table->addIndex($index);
        }
    }

    private function buildForeignKeys() {
        foreach ($this->foreignKeys as $data) {
            $name = trim($data[0]);
            if (isset($this->binds[$name])) {
                $name = $this->binds[$name];
            }
            $localColumnNames = $data[1];
            if (isset($this->binds[$localColumnNames])) {
                $localColumnNames = $this->binds[$localColumnNames];
            }
            $localColumnNames = explode(',', $localColumnNames);
            $localColumns = array();
            foreach ($localColumnNames as &$columnName) {
                $columnName = trim($columnName);
                if (isset($this->binds[$columnName])) {
                    $columnName = $this->binds[$columnName];
                }
                $localColumns []= $this->columns->$columnName;
            }


            $referenceTableName = trim($data[2]);
            if (isset($this->binds[$referenceTableName])) {
                $referenceTableName = $this->binds[$referenceTableName];
            }
            $referenceColumnNames = $data[3];
            if (isset($this->binds[$referenceColumnNames])) {
                $referenceColumnNames = $this->binds[$referenceColumnNames];
            }
            $referenceColumnNames = explode(',', $referenceColumnNames);
            $referenceColumns = array();
            foreach ($referenceColumnNames as &$columnName) {
                $columnName = trim($columnName);
                if (isset($this->binds[$columnName])) {
                    $columnName = $this->binds[$columnName];
                }
                $column = new Column();
                $column->schemaName = $columnName;
                $column->table = new Table(null, null, $referenceTableName);

                $referenceColumns []= $column;
            }

            $foreignKey = new Database\Definition\ForeignKey($localColumns, $referenceColumns);

            //var_dump($data);

            // ON UPDATE
            if ($data[4]) {
                $foreignKey->onUpdate = $data[4];
            }

            // ON DELETE
            if ($data[5]) {
                $foreignKey->onDelete = $data[5];
            }

            $foreignKey->setName($name);
            $this->table->addForeignKey($foreignKey);
        }
    }

    public function getDefinition() {
        //echo $this->statement;

        $this->tokenize();
        $this->bracketize();
        $this->parseLines();

        //print_r(array_keys((array)$this->columns));

        $this->table = new Table($this->columns, $this->database, $this->tableName);

        $this->buildIndexes();
        $this->buildForeignKeys();

        return $this->table;
    }


    public function getTypeByString($type) {
        $phpType = Column::STRING;
        switch (true) {
            case 'bigint' === substr($type, 0, 6):
            case 'int' === substr($type, 0, 3):
            case 'mediumint' === substr($type, 0, 9):
            case 'smallint' === substr($type, 0, 8):
            case 'tinyint' === substr($type, 0, 7):
                $phpType = Column::INTEGER;
                break;

            case 'decimal' === substr($type, 0, 7):
            case 'double' === $type:
            case 'float' === $type:
                $phpType = Column::FLOAT;
                break;

            case 'date' === $type:
            case 'datetime' === $type:
            case 'timestamp' === $type:
                $phpType = Column::TIMESTAMP;
                break;

        }
        return $phpType;
    }





}