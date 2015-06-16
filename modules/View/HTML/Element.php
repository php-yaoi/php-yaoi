<?php

namespace Yaoi\View\HTML;

use Yaoi\BaseClass;
use Yaoi\IsEmpty;
use Yaoi\View\Renderer;

class Element extends BaseClass implements Renderer
{
    protected $id;
    protected $classes = array();
    protected $attributes = array();
    protected $tag;
    protected $content;

    public function addClass($class)
    {
        if (!isset($this->classes[$class])) {
            $this->classes[$class] = $class;
        }
        return $this;
    }

    public function removeClass($class)
    {
        if (isset($this->classes[$class])) {
            unset($this->classes[$class]);
        }
        return $this;
    }

    public function setId($id = null)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $name
     * @param null $value
     * @return static
     */
    public function setAttribute($name, $value = null)
    {
        if (null === $value) {
            if (array_key_exists($name, $this->attributes)) {
                unset($this->attributes[$name]);
            }
        } else {
            $this->attributes[$name] = $value;
        }
        return $this;
    }


    public function __toString()
    {
        ob_start();
        $this->render();
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    protected $children = array();

    public function appendChild($child)
    {
        $this->children [] = $child;
        return $this;
    }

    public function prependChild($child)
    {
        array_unshift($this->children, $child);
        return $this;
    }

    public function isEmpty()
    {
        if ($this->content instanceof IsEmpty) {
            return $this->content->isEmpty();
        } else {
            return empty($this->content);
        }
    }

    protected $headRendered = false;

    protected function renderHead()
    {
        $this->headRendered = true;
        if (null === $this->tag) {
            return;
        }

        echo '<' . $this->tag
            . (isset($this->id) ? ' id="' . $this->id . '"' : '')
            . (empty($this->classes) ? '' : ' class="' . implode(' ', $this->classes) . '"');
        if ($this->attributes) {
            foreach ($this->attributes as $attribute => $value) {
                echo ' ' . $attribute . '="' . $this->escapeValue($value) . '"';
            }
        }
    }

    protected $contentRendered;

    protected function renderContentChunk($content)
    {
        $contentExists = false;
        if (($content instanceof IsEmpty) && !$content->isEmpty()) {
            $contentExists = true;
        } elseif ($content !== null) {
            $contentExists = true;
        }

        if ($contentExists) {
            if (!$this->contentRendered) {
                $this->contentRendered = 1;
                if ($this->tag) {
                    echo '>';
                }
            }

            echo $content;
        }
    }

    protected $tailRendered = false;

    protected function renderTail()
    {
        $this->tailRendered = true;

        if (null === $this->tag) {
            return;
        }

        if ($this->contentRendered) {
            echo '</' . $this->tag . '>';
        } else {
            echo ' />';
        }
    }

    public function render()
    {
        $this->headRendered = false;
        $this->contentRendered = false;
        $this->tailRendered = false;

        $this->renderHead();
        $this->renderContentChunk($this->content);
        $this->renderTail();
        return $this;
    }


    public static function escapeContent($s)
    {
        return str_replace('<', '&lt;', $s);
    }

    public static function escapeValue($s)
    {
        return str_replace(array('"', "'"), array('&quot;', '&#39;'), $s);
    }


    public function onClick($jsCode)
    {
        return $this->setAttribute('onclick', $jsCode);
    }


}