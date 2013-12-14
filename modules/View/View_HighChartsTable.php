<?php

class View_HighChartsTable implements View_TableRenderer {
    private $tableData = array();

    public function add($row)
    {
        $this->tableData []= $row;
    }

    public function setRows(&$rows)
    {
    }

    public function render()
    {
    }

    public function isEmpty()
    {
        return empty($this->tableData);
    }

} 