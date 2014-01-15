<?php

class View_Null extends Base_Class implements View_Renderer {
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