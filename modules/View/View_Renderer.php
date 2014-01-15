<?php

interface View_Renderer extends Is_Empty {
    public function render();
    public function __toString();
}
