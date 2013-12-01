<?php

class View_HTMLElement implements  View_Renderer {
    protected $id;
    protected $classes = array();
    protected $attributes;
    protected $tag;
    protected $content;

    public function addClass($class) {
        if (!isset($this->classes[$class])) {
            $this->classes[$class] = $class;
        }
        return $this;
    }

    public function removeClass($class) {
        if (isset($this->classes[$class])) {
            unset($this->classes[$class]);
        }
        return $this;
    }


    public function render() {
        echo $this->__toString();
        return $this;
    }

    public function isEmpty() {

    }

    public function __toString() {
        $code = '<' . $this->tag
            . (isset($this->id) ? ' id="' . $this->id . '"': '')
            . (empty($this->classes) ? '' : ' class="' . $this->id .'"')
        ;
        if ($this->attributes) {
            foreach ($this->attributes as $attribute => $value) {
                $code .= ' ' . $attribute . '="' . $value . '"';
            }
        }
        if ($content = (string)$this->content) {
            $code .= '>';
            $code .= $content;
            $code .= '</' . $this->tag . '>';
        }
        else {
            $code .= ' />';
        }
        return $code;
    }
} 