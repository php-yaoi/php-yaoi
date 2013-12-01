<?php

interface View_TableRenderer {
    public function add($row);
    public function setRows(&$rows);
    public function render();
}