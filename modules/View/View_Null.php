<?php

class View_Null implements View_Renderer {
    public function isEmpty()
    {
        return true;
    }

    public function render()
    {
    }

} 