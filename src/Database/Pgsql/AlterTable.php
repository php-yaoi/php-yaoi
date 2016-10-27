<?php

namespace Yaoi\Database\Pgsql;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\ForeignKey;
use Yaoi\Database\Definition\Index;
use Yaoi\Sql\Expression;
use Yaoi\Sql\Symbol;

class AlterTable extends \Yaoi\Sql\AlterTable
{
    protected function processIndexes() {
        /** @var Index[] $beforeIndexes */
        $beforeIndexes = array();
        foreach ($this->before->indexes as $index) {
            $beforeIndexes [$index->getName()]= $index;
        }

        foreach ($this->after->indexes as $index) {
            $indexName = $index->getName();
            if (!isset($beforeIndexes[$indexName])) {
                $this->add($this->database()->expr('CREATE '
                    . ($index->type === Index::TYPE_UNIQUE ? 'UNIQUE ' : '')
                    . 'INDEX ? ON ? (?)',
                    new Symbol($index->getName()), new Symbol($this->before->schemaName), Symbol::prepareColumns($index->columns))
                );
            }
            else {
                unset($beforeIndexes[$indexName]);
            }
        }
        foreach ($beforeIndexes as $index) {
            if ($index->type === Index::TYPE_UNIQUE) {
                $this->alterLines->commaExpr('DROP CONSTRAINT ?', new Symbol($index->getName()));
            }
            else {
                $this->add($this->database()->expr('DROP INDEX ?', new Symbol($index->getName())));
            }
        }

    }

    protected function processColumns()
    {
        $beforeColumns = $this->before->getColumns(true, true);
        foreach ($this->after->getColumns(true, true) as $columnName => $afterColumn) {
            $afterTypeString = $afterColumn->getTypeString();

            if (!isset($beforeColumns[$columnName])) {
                $this->alterLines->commaExpr('ADD COLUMN ? ' . $afterTypeString, new Symbol($afterColumn->schemaName));
            }
            else {
                $beforeColumn = $beforeColumns[$columnName];
                if ($beforeColumn->getTypeString() !== $afterTypeString) {
                    //var_dump('MODIFY:' . $beforeColumn->schemaName, $beforeColumn->getTypeString(), $afterTypeString);
                    $afterType = $afterColumn
                        ->copy()
                        ->setFlag(Column::NOT_NULL, false)
                        ->setDefault(false)
                        ->getTypeString();
                    $this->alterLines->commaExpr('ALTER COLUMN ? TYPE ' . $afterType, new Symbol($afterColumn->schemaName));

                    if (($beforeColumn->flags & Column::NOT_NULL) !== ($afterColumn->flags & Column::NOT_NULL)) {
                        if ($afterColumn->flags & Column::NOT_NULL) {
                            $this->alterLines->commaExpr('ALTER COLUMN ? SET NOT NULL', new Symbol($afterColumn->schemaName));
                        }
                        else {
                            $this->alterLines->commaExpr('ALTER COLUMN ? DROP NOT NULL', new Symbol($afterColumn->schemaName));
                        }
                    }


                    if ($afterColumn->getDefault() !== $beforeColumn->getDefault()) {
                        if (null === $afterColumn->getDefault()) {
                            $this->alterLines->commaExpr('ALTER COLUMN ? DROP DEFAULT', new Symbol($afterColumn->schemaName));
                        }
                        else {
                            $this->alterLines->commaExpr('ALTER COLUMN ? SET DEFAULT ?',
                                new Symbol($afterColumn->schemaName), $afterColumn->getDefault());
                        }
                    }

                }
                unset($beforeColumns[$columnName]);
            }
        }
        foreach ($beforeColumns as $columnName => $beforeColumn) {
            $this->alterLines->commaExpr('DROP COLUMN ?', new Symbol($beforeColumn->schemaName));
        }
    }


    protected function processForeignKeys()
    {
        /** @var ForeignKey[] $beforeForeignKeys */
        $beforeForeignKeys = array();
        if (!$this->before->disableForeignKeys) {
            foreach ($this->before->getForeignKeys() as $foreignKey) {
                $beforeForeignKeys [$foreignKey->getName()] = $foreignKey;
            }
        }
        $afterForeignKeys = $this->after->getForeignKeys();
        if ($this->after->disableForeignKeys) {
            $afterForeignKeys = array();
        }
        foreach ($afterForeignKeys as $foreignKey) {
            if (!isset($beforeForeignKeys[$foreignKey->getName()])) {
                $this->addFkExpression->commaExpr('ADD');
                $this->addFkExpression->appendExpr($this->database()->getUtility()->generateForeignKeyExpression($foreignKey));
            } else {
                unset($beforeForeignKeys[$foreignKey->getName()]);
            }
        }
        foreach ($beforeForeignKeys as $foreignKey) {
            $this->alterLines->commaExpr('DROP CONSTRAINT IF EXISTS ?', new Symbol($foreignKey->getName()));
        }

    }


}