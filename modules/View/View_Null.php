<?php

use Yaoi\BaseClass;

class View_Null extends BaseClass implements View_Renderer {
    public function isEmpty()
    {
        return true;
    }

    public function render()
    {
    }

    public function __toString() {
        return '';
    }

} 