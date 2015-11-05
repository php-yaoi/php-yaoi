<?php

namespace Yaoi\View;

class Stack extends Hardcoded implements Renderer
{
    /**
     * @var Renderer[]
     */
    private $elements = array();

    public function isEmpty()
    {
        return empty($this->elements);
    }

    public function push(Renderer $element)
    {
        $this->elements [] = $element;
        return $this;
    }

    public function render()
    {
        foreach ($this->elements as $element) {
            $element->render();
        }
        return $this;
    }

}