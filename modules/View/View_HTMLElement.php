<?php

class View_HTMLElement extends Base_Class implements View_Renderer {
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

    public function setId($id = null) {
        $this->id = $id;
        return $this;
    }

    public function setAttribute($name, $value = null) {
        if (null === $value) {
            if (array_key_exists($name, $this->attributes)) {
                unset($this->attributes[$name]);
            }
        }
        else {
            $this->attributes[$name] = $value;
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

    public function isEmpty() {
        if ($this->content instanceof Is_Empty) {
            return $this->content->isEmpty();
        }
        else {
            return empty($this->content);
        }
    }

    protected $headRendered = false;
    protected function renderHead() {
        $this->headRendered = true;
        if (null === $this->tag) {
            return;
        }

        echo '<' . $this->tag
            . (isset($this->id) ? ' id="' . $this->id . '"': '')
            . (empty($this->classes) ? '' : ' class="' . implode(' ', $this->classes) .'"')
        ;
        if ($this->attributes) {
            foreach ($this->attributes as $attribute => $value) {
                echo ' ' . $attribute . '="' . $value . '"';
            }
        }
    }

    private $contentExists;
    protected function renderContentChunk($content) {
        $contentExists = false;
        if (($content instanceof Is_Empty) && !$content->isEmpty()) {
            $contentExists = true;
        }
        elseif ($content !== null) {
            $contentExists = true;
        }

        if ($contentExists) {
            if (!$this->contentExists) {
                $this->contentExists = 1;
                echo '>';
            }

            echo $content;
        }
    }

    protected $tailRendered = false;
    protected function renderTail() {
        $this->tailRendered = true;

        if (null === $this->tag) {
            return;
        }

        if ($this->contentExists) {
            echo '</' . $this->tag . '>';
        }
        else {
            echo ' />';
        }
    }

    public function render() {
        $this->renderHead();
        $this->renderContentChunk($this->content);
        $this->renderTail();
        return $this;
    }


    public static function escapeContent($s) {
        return str_replace('<', '&lt;', $s);
    }

    public static function escapeValue($s) {
        return str_replace(array('"', "'"), array('&quot;', '&#39;'), $s);
    }

} 