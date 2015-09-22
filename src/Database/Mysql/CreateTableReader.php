<?php

namespace Yaoi\Database\Mysql;

use Yaoi\Database;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;
use Yaoi\Sql\Symbol;
use Yaoi\String\Parser;
use Yaoi\String\Tokenizer;

class CreateTableReader
{
    private $statement;
    private $database;
    public function __construct($statement, Database\Contract $database) {
        $this->database = $database;
        $this->statement = $statement;
    }

    private $tokens;

    /** @var Tokenizer\Token[]  */
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

    const BIND_PREFIX = ':B';
    const BIND_POSTFIX = 'B:';

    private function resolve($index, $recursive = false) {
        if (strpos($index, self::BIND_PREFIX) !== false) {
            $index = (string)Parser::create($index)->inner(self::BIND_PREFIX, self::BIND_POSTFIX);
        }

        $key = self::BIND_PREFIX . $index . self::BIND_POSTFIX;
        if (!isset($this->binds[$key])) {
            return $index;
        }

        $bind = $this->binds[$key];
        if ($bind instanceof Tokenizer\Token) {
            return $bind->unEscapedContent;
        }
        elseif ($bind instanceof Tokenizer\Parsed) {
            return $bind;
        }
    }

    private function tokenize2() {
        $tokenizer = Utility::create()->getStatementTokenizer();
        $tokens = $tokenizer->tokenize($this->statement);
        $tokens->bindKeyPostfix = self::BIND_POSTFIX;
        $tokens->bindKeyPrefix = self::BIND_PREFIX;
        $expression = $tokens->getExpression(array('#', ' --'));
        $this->binds = $expression->getBinds();
        $statement = new Parser($expression->getStatement());
        $statement->inner('CREATE TABLE', self::BIND_PREFIX);
        $this->tableName = $this->resolve($statement->inner(null, self::BIND_POSTFIX));
        $lines = $this->resolve($statement->inner(self::BIND_PREFIX, self::BIND_POSTFIX));
        if ($lines instanceof Tokenizer\Parsed) {
            $expression = $lines->getExpression();
            $this->deBracketed = $expression->getStatement();
            $this->deBracketed = preg_replace('/\s+/', ' ', $this->deBracketed);

            $this->binds = $expression->getBinds();
        }
        else {
            throw new \Exception('Malformed');
        }
    }

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
        $line = strtoupper(trim($line));
        $parser = new Parser($line);
        $columnName = $this->resolve($parser->inner(self::BIND_PREFIX, self::BIND_POSTFIX));

        $type = (string)$parser->inner(' ', ' ');
        $unsigned = strpos($line, 'UNSIGNED') !== false;
        $notNull = strpos($line, 'NOT NULL') !== false;
        $autoId = strpos($line, 'AUTO_INCREMENT') !== false;
        $default = $parser->inner('DEFAULT ');
        if (!$default->isEmpty()) {
            $default = (string)$default;
            if ('NULL' === $default) {
                $default = null;
            }
            elseif (strpos($default, self::BIND_PREFIX) !== false) {
                $default = $this->resolve($default);
            }
        }
        else {
            $default = false;
        }

        $flags = $this->getTypeByString($type);

        if ($notNull) {
            $flags += Column::NOT_NULL;
        }

        if ($autoId) {
            $flags += Column::AUTO_ID;
        }

        if ($unsigned) {
            $flags += Column::UNSIGNED;
        }

        $column = new Column($flags);

        if ($length = (string)$parser->setOffset(0)->inner('VARCHAR(', ')')) {
            $column->setStringLength($length, false);
        }
        elseif ($length = (string)$parser->setOffset(0)->inner('CHAR(', ')')) {
            $column->setStringLength($length, true);
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
                $data = explode(' ', trim($parser->inner('KEY ')));
                $indexColumns = $this->resolve($data[0]);
                $this->indexes []= array(Index::TYPE_PRIMARY, Index::TYPE_PRIMARY, $indexColumns);
            }
            elseif ($parser->starts('UNIQUE KEY')) {
                $data = explode(' ', trim($parser->inner('KEY ')));
                $indexName = $this->resolve($data[0]);
                $indexColumns = $this->resolve($data[1]);
                $this->indexes []= array(Index::TYPE_UNIQUE, $indexName, $indexColumns);
            }
            elseif ($parser->starts('KEY')) {
                $data = explode(' ', trim($parser->inner('KEY ')));
                $indexName = $this->resolve($data[0]);
                $indexColumns = $this->resolve($data[1]);
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
            var_dump($indexData);

            $type = $indexData[0];
            $name = $this->resolve(trim($indexData[1]));

            $indexColumns = array();
            foreach (explode(',', $indexData[2]) as $columnName) {
                var_dump($columnName);
                $columnName = $this->resolve(trim($columnName));
                var_dump($columnName);
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

        //$this->tokenize();
        //$this->bracketize();
        //$this->parseLines();


        $this->tokenize2();
        $this->parseLines();



        //print_r(array_keys((array)$this->columns));

        $this->table = new Table($this->columns, $this->database, $this->tableName);

        $this->buildIndexes();
        $this->buildForeignKeys();

        print_r($this->table);

        die('!');

        return $this->table;
    }


    public function getTypeByString($type) {
        $phpType = Column::STRING;
        switch (true) {
            case 'BIGINT' === substr($type, 0, 6):
            case 'INT' === substr($type, 0, 3):
            case 'MEDIUMINT' === substr($type, 0, 9):
            case 'SMALLINT' === substr($type, 0, 8):
            case 'TINYINT' === substr($type, 0, 7):
                $phpType = Column::INTEGER;
                break;

            case 'DECIMAL' === substr($type, 0, 7):
            case 'DOUBLE' === $type:
            case 'FLOAT' === $type:
                $phpType = Column::FLOAT;
                break;

            case 'DATE' === $type:
            case 'DATETIME' === $type:
            case 'TIMESTAMP' === $type:
                $phpType = Column::TIMESTAMP;
                break;

        }
        return $phpType;
    }





}