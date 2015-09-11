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
    private $bindIndex = 0;

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
    }


    public function getDefinition() {
        $statement = $this->statement;

        echo $statement;

        $this->tokenize();
        //echo $this->deQuoted;



        $parser = new Parser($this->deQuoted);
        $tableName = trim($parser->inner('CREATE TABLE ', '('));
        if (isset($this->binds[$tableName])) {
            $tableName = $this->binds[$tableName];
        }
        $lines = $parser->inner(null, ')', true);

        $bracketTokenizer = new Tokenizer();
        $bracketTokenizer->addQuote('(', ')');
        $bracketTokens = $bracketTokenizer->tokenize($lines);

        $deBracketed = '';
        foreach ($bracketTokens as $token) {
            if (is_string($token)) {
                $deBracketed .= $token;
            }
            else {
                $this->binds ['?' . $this->bindIndex . '?']= $token[0];
                $deBracketed .= '(?' . $this->bindIndex . '?)';
                ++$this->bindIndex;
            }
        }

        //print_r($this->binds);
        //echo $deBracketed;

        $lines = explode(',', $deBracketed);
        $indexes = array();
        $foreignKeys = array();

        $columns = new \stdClass();
        foreach ($lines as $line) {
            $line = trim($line);
            $parser = new Parser($line);

            if (Utils::starts($line, 'PRIMARY KEY')) {
                $indexColumns = trim($parser->inner('(', ')'));
                $indexes []= array(Index::TYPE_PRIMARY, Index::TYPE_PRIMARY, $indexColumns);
            }
            elseif (Utils::starts($line, 'UNIQUE KEY')) {
                $indexName = trim($parser->inner('KEY', '('), '`');
                $indexColumns = trim($parser->inner(null, ')'));
                $indexes []= array(Index::TYPE_UNIQUE, $indexName, $indexColumns);
            }
            elseif (Utils::starts($line, 'KEY')) {
                $indexName = trim($parser->inner('KEY', '('), '`');
                $indexColumns = trim($parser->inner(null, ')'));
                $indexes []= array(Index::TYPE_KEY, $indexName, $indexColumns);
            }
            elseif (Utils::starts($line, 'CONSTRAINT')) {
                $indexName = trim($parser->inner('CONSTRAINT', 'FOREIGN KEY'), '`');
                $indexColumns = trim($parser->inner('(', ')'));
                $referenceName = trim($parser->inner('REFERENCES', '('), '`');
                $referenceColumns = trim($parser->inner(null, ')'));
                $foreignKeys []= array($indexName, $indexColumns, $referenceName, $referenceColumns);
            }
            else {
                $columnName = trim($parser->inner(null, ' '), '`');
                if (isset($this->binds[$columnName])) {
                    $columnName = $this->binds[$columnName];
                }
                $type = $parser->inner(null, ' ');
                $notNull = strpos($line, 'NOT NULL') !== false;
                $autoId = strpos($line, 'AUTO_INCREMENT') !== false;
                $default = (string)$parser->inner('DEFAULT ');
                if ('NULL' === $default) {
                    $default = null;
                }
                elseif (strpos($default, '?') !== false) {
                    $default = strtr($default, $this->binds);
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
                $columns->$columnName = $column;
            }

        }


        $table = new Table($columns, $this->database, $tableName);
        //var_dump($indexes);
        var_dump($foreignKeys);
        foreach ($indexes as $indexData) {
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
                $indexColumns []= $columns->$columnName;
            }
            $index = new Index($indexColumns);
            $index->setName($name);
            $index->setType($type);
            $table->addIndex($index);
        }


        foreach ($foreignKeys as $data) {
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
                $localColumns []= $columns->$columnName;
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
            $foreignKey->setName($name);
            $table->addForeignKey($foreignKey);
        }

        //print_r($table);

        $tail = $parser->inner();

        return $table;
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