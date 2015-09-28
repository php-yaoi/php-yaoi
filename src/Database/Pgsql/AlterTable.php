<?php

namespace Yaoi\Database\Pgsql;

use Yaoi\Database\Definition\Index;
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
                $this->add($this->database->expr('CREATE '
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
                $this->add($this->database->expr('DROP INDEX ?', new Symbol($index->getName())));
            }
        }

    }


}