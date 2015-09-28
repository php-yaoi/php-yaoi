<?php

namespace Yaoi\Database\Mysql;

use Yaoi\Database;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;
use Yaoi\String\Parser;
use Yaoi\String\Lexer;

class CreateTableReader
{
    private $statement;
    private $database;
    public function __construct($statement, Database\Contract $database) {
        $this->database = $database;
        $this->statement = $statement;
    }

    /** @var Lexer\Token[]  */
    private $binds = array();
    private $tableName;
    /** @var  \stdClass */
    private $columns;
    private $indexes = array();
    private $foreignKeys = array();
    /** @var  Table */
    private $table;


    const BIND_PREFIX = ':B';
    const BIND_POSTFIX = 'B:';

    /**
     * @param $index
     * @return string|Lexer\Token|Lexer\Parsed
     */
    private function resolve($index) {
        if (strpos($index, self::BIND_PREFIX) !== false) {
            $index = (string)Parser::create($index)->inner(self::BIND_PREFIX, self::BIND_POSTFIX);
        }

        $key = self::BIND_PREFIX . $index . self::BIND_POSTFIX;
        if (!isset($this->binds[$key])) {
            return $index;
        }

        $bind = $this->binds[$key];
        if ($bind instanceof Lexer\Token) {
            return $bind->unEscapedContent;
        }
        elseif ($bind instanceof Lexer\Parsed) {
            return $bind;
        }
    }

    /** @var  Lexer\Parsed[] */
    private $lines;
    private function tokenize() {
        $tokenizer = Utility::create()->getStatementTokenizer();

        $tokens = $tokenizer->tokenize($this->statement);
        $renderer = new Lexer\Renderer();
        $renderer
            ->setBindKey(self::BIND_PREFIX, self::BIND_POSTFIX)
            ->strip('#', ' --')
            ->keepBoundaries('(');

        $expression = $renderer->getExpression($tokens);
        $this->binds = $expression->getBinds();

        $statement = new Parser($expression->getStatement());
        $statement->inner('CREATE TABLE', self::BIND_PREFIX);

        $this->tableName = $this->resolve($statement->inner(null, self::BIND_POSTFIX));
        /** @var Lexer\Parsed $lines */
        $lines = $this->resolve($statement->inner('(', ')'));
        $this->lines = $lines->split(',');
    }

    private function parseColumn(Parser $parser) {
        $columnName = $this->resolve($parser->inner(self::BIND_PREFIX, self::BIND_POSTFIX));

        $type = (string)$parser->inner(' ', ' ');
        $unsigned = $parser->contain('UNSIGNED');
        $notNull = $parser->contain('NOT NULL');
        $autoId = $parser->contain('AUTO_INCREMENT');
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

        $this->columns->$columnName = $column;
    }

    private function parseLines() {
        $this->columns = new \stdClass();
        $this->indexes = array();
        $this->foreignKeys = array();

        $renderer = new Lexer\Renderer();
        $renderer
            ->keep('(')
            ->strip('-- ', '#')
            ->setBindKey(self::BIND_PREFIX, self::BIND_POSTFIX);


        foreach ($this->lines as $line) {
            $expression = $renderer->getExpression($line);
            $statement = $expression->getStatement();

            $this->binds = $expression->getBinds();

            $statement = strtoupper(trim($statement));
            $parser = new Parser($statement);

            if ($parser->starts('PRIMARY KEY')) {
                $indexColumns = $parser->inner('(', ')')->explode(',');
                foreach ($indexColumns as &$columnName) {
                    $columnName = $this->resolve($columnName);
                }
                $this->indexes []= array(Index::TYPE_PRIMARY, Index::TYPE_PRIMARY, $indexColumns);
            }
            elseif ($parser->starts('UNIQUE KEY')) {
                $indexName = $this->resolve(trim($parser->inner('KEY', '(')));
                $indexColumns = $parser->inner(null, ')')->explode(',');

                foreach ($indexColumns as &$columnName) {
                    $columnName = $this->resolve($columnName);
                }
                $this->indexes []= array(Index::TYPE_UNIQUE, $indexName, $indexColumns);
            }
            elseif ($parser->starts('KEY')) {
                $indexName = $this->resolve(trim($parser->inner('KEY', '(')));
                $indexColumns = $parser->inner(null, ')')->explode(',');
                foreach ($indexColumns as &$columnName) {
                    $columnName = $this->resolve($columnName);
                }
                $this->indexes []= array(Index::TYPE_KEY, $indexName, $indexColumns);
            }
            elseif ($parser->starts('CONSTRAINT')) {
                $this->parseConstraint($parser);
            }
            else {
                $this->parseColumn($parser);
            }

        }
    }


    private function parseConstraint(Parser $parser) {
        $indexName = $this->resolve($parser->inner('CONSTRAINT', 'FOREIGN KEY'));
        $indexColumns = $parser->inner('(', ')')->explode(',');
        foreach ($indexColumns as &$columnName) {
            $columnName = $this->resolve($columnName);
        }

        $referenceName = $this->resolve($parser->inner('REFERENCES', '('));
        $referenceColumns = $parser->inner(null, ')')->explode(',');
        foreach ($referenceColumns as &$columnName) {
            $columnName = $this->resolve($columnName);
        }

        $ons = $parser->inner()->explode('ON ');
        $onUpdate = $onDelete = null;
        if (count($ons) > 1) {
            unset($ons[0]);
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
            $name = $indexData[1];

            $columns = array();
            foreach ($indexData[2] as $columnName) {
                $columns []= $this->columns->$columnName;
            }
            $index = new Index($columns);
            $index->setName($name);
            $index->setType($type);
            $this->table->addIndex($index);
        }
    }

    private function buildForeignKeys() {
        foreach ($this->foreignKeys as $data) {
            $name = $data[0];
            $localColumnNames = $data[1];
            $localColumns = array();
            foreach ($localColumnNames as &$columnName) {
                $localColumns []= $this->columns->$columnName;
            }


            $referenceTableName = $data[2];
            $referenceColumnNames = $data[3];

            $referenceColumns = array();
            foreach ($referenceColumnNames as &$columnName) {
                $column = new Column();
                $column->schemaName = $columnName;
                $column->table = new Table(null, null, $referenceTableName);

                $referenceColumns []= $column;
            }

            $foreignKey = new Database\Definition\ForeignKey($localColumns, $referenceColumns);

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
        $this->tokenize();
        $this->parseLines();

        $this->table = new Table($this->columns, $this->database, $this->tableName);

        $this->buildIndexes();
        $this->buildForeignKeys();

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