<?php

abstract class View_Hardcoded extends Base_Class implements View_Renderer {
    public function isEmpty() {
        return false;
    }

    public function __toString() {
        ob_start();
        $this->render();
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }


}