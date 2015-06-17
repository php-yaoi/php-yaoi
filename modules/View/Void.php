<?php

namespace Yaoi\View;
use Yaoi\BaseClass;

class Void extends BaseClass implements Renderer
{
    public function isEmpty()
    {
        return true;
    }

    public function render()
    {
    }

    public function __toString()
    {
        return '';
    }

}