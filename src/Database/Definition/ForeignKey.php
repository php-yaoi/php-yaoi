<?php

namespace Yaoi\Database\Definition;

use Yaoi\BaseClass;

class ForeignKey extends BaseClass
{
    const CASCADE = 'cascade';
    const SET_NULL = 'set_null';
    const SET_DEFAULT = 'set_default';
    const NO_ACTION = 'no_action';
    const RESTRICT = 'restrict';

    const PARENT = 'parent';
    const CHILD = 'child';

    private $onUpdate;
    private $onDelete;

    /**
     * @param Column[] $childColumns
     * @param Column[] $parentColumns
     * @param string $onUpdate
     * @param string $onDelete
     */
    public function __construct(array $childColumns,
                                array $parentColumns,
                                $onUpdate = self::NO_ACTION,
                                $onDelete = self::NO_ACTION) {
        $childColumns = array_values($childColumns);
        $parentColumns = array_values($parentColumns);

        if (count($childColumns) !== count($parentColumns)) {
            throw new Exception('Foreign key column count mismatch', Exception::FK_COUNT_MISMATCH);
        }

        $this->childColumns = $childColumns;
        $this->parentColumns = $parentColumns;
        $this->onUpdate = $onUpdate;
        $this->onDelete = $onDelete;
    }


    /**
     * @return Table
     */
    public function getReferencedTable() {
        return $this->parentColumns[0]->table;
    }

    /** @var Column[] */
    private $parentColumns = array();

    /**
     * @var Column[]
     */
    private $childColumns = array();

    public function getChildColumns() {
        return $this->childColumns;
    }

    public function getParentColumns() {
        return $this->parentColumns;
    }

    private $name;
    public function getName() {
        if (null === $this->name) {
            $this->name = 'fk';
            foreach ($this->childColumns as $column) {
                $this->name .= '_' . $column->table->schemaName;
                break;
            }
            foreach ($this->childColumns as $column) {
                $this->name .= '_' . $column->schemaName;
            }
            foreach ($this->parentColumns as $column) {
                $this->name .= '_' . $column->table->schemaName;
                break;
            }
            foreach ($this->parentColumns as $column) {
                $this->name .= '_' . $column->schemaName;
            }

            if (strlen($this->name) > 64) {
                $this->name = md5($this->name);
            }
        }
        return $this->name;
    }
}