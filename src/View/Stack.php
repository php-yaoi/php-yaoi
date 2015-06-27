<?php

namespace Yaoi\View;

use Yaoi\BaseClass;

class Stack extends BaseClass implements Renderer
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

    public function __toString()
    {
        ob_start();
        try {
            $this->render();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
}