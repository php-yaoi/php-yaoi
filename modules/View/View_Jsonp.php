<?php

/**
 * Class View_Jsonp
 * @method static View_Jsonp create($callback, $data = null)
 */
class View_Jsonp extends Base_Class implements View_Renderer {
    private $callback;
    private $data;


    public function __construct($callback, $data = null) {
        $this->data = $data;
        $this->callback = $callback;
    }

    public function isEmpty()
    {
        return empty($this->data);
    }

    public function render()
    {
        header('Content-Type: text/javascript; charset=utf8');
        echo $this->__toString();
        return $this;
    }

    public function __toString()
    {
        $result = $this->callback . '(' . json_encode($this->data) . ');';
        return $result;
    }

} 