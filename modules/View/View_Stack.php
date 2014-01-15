<?php

class View_Stack extends Base_Class implements View_Renderer {
    /**
     * @var View_Renderer[]
     */
    private $elements = array();

    public function isEmpty() {
        return empty($this->elements);
    }

    public function push(View_Renderer $element) {
        $this->elements []= $element;
        return $this;
    }

    public function render() {
        foreach ($this->elements as $element) {
            $element->render();
        }
        return $this;
    }

    public function __toString() {
        ob_start();
        $this->render();
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }
}