<?php

interface View_TableRenderer extends View_Renderer {
    public function add($row);
    public function setRows(&$rows);
}