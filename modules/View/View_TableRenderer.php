<?php

abstract class View_TableRenderer extends View_HTMLElement {
    protected $rows = array();

    public function __construct(&$rows = null) {
        if (null !== $rows) {
            $this->setRows($rows);
        }
    }

    public function setRows(&$rows) {
        if (!is_array($rows) || $rows instanceof Iterator) {
            throw new View_Exception('Wrong data type', View_Exception::WRONG_DATA_TYPE);
        }
        $this->rows = &$rows;
        return $this;
    }
}