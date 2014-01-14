<?php

abstract class View_Table_Stream extends View_Table_Renderer {
    public function push($row) {
        if (!$this->headRendered) {
            $this->renderHead();
        }

        ob_start();
        $this->renderRow($row);
        $tr = ob_get_contents();
        ob_end_clean();
        $this->renderContentChunk($tr);

        return $this;
    }

    abstract protected function renderRow($row);

    public function render() {
        $this->renderHead();

        foreach ($this->rows as $row) {
            $this->push($row);
        }

        $this->renderTail();
        return $this;
    }

    public function __destruct() {
        if ($this->headRendered && !$this->tailRendered) {
            $this->renderTail();
        }
    }

} 