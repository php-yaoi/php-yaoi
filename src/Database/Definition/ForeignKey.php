<?php

namespace Yaoi\Database\Definition;

use Yaoi\BaseClass;

class ForeignKey extends BaseClass
{
    const CASCADE = 'CASCADE';
    const SET_NULL = 'SET NULL';
    const SET_DEFAULT = 'SET DEFAULT';
    const NO_ACTION = 'NO ACTION';
    const RESTRICT = 'RESTRICT';

    const PARENT = 'parent';
    const CHILD = 'child';

    public $onUpdate;
    public $onDelete;

    /**
     * @param Column[] $localColumns
     * @param Column[] $referenceColumns
     * @param string $onUpdate
     * @param string $onDelete
     * @throws Exception
     */
    public function __construct(array $localColumns,
                                array $referenceColumns,
                                $onUpdate = self::NO_ACTION,
                                $onDelete = self::NO_ACTION) {
        $localColumns = array_values($localColumns);
        $referenceColumns = array_values($referenceColumns);

        if (count($localColumns) !== count($referenceColumns)) {
            throw new Exception('Foreign key column count mismatch', Exception::FK_COUNT_MISMATCH);
        }

        $this->localColumns = $localColumns;
        $this->referenceColumns = $referenceColumns;
        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;
    }


    public function setOnUpdate($onUpdate = self::NO_ACTION) {
        $this->onUpdate = $onUpdate;
        return $this;
    }

    public function setOnDelete($onDelete = self::NO_ACTION) {
        $this->onDelete = $onDelete;
        return $this;
    }

    /**
     * @return Table
     */
    public function getReferencedTable() {
        return $this->referenceColumns[0]->table;
    }

    /** @var Column[] */
    private $referenceColumns = array();

    /**
     * @var Column[]
     */
    private $localColumns = array();

    public function getLocalColumns() {
        return $this->localColumns;
    }

    public function getReferenceColumns() {
        return $this->referenceColumns;
    }

    private $name;
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getName() {
        if (null === $this->name) {
            $this->name = 'fk';
            foreach ($this->localColumns as $column) {
                $this->name .= '_' . $column->table->schemaName;
                break;
            }
            foreach ($this->localColumns as $column) {
                $this->name .= '_' . $column->schemaName;
            }
            foreach ($this->referenceColumns as $column) {
                $this->name .= '_' . $column->table->schemaName;
                $this->name .= '_' . $column->schemaName;
            }

            if (strlen($this->name) > 64) {
                $this->name = md5($this->name);
            }
        }
        return $this->name;
    }
}