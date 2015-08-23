<?php

namespace Yaoi\String;

use Traversable;
use Yaoi\BaseClass;
use Yaoi\IsEmpty;

class Parser extends BaseClass implements IsEmpty
{
    private $string;

    private $offset = 0;

    public function __construct($string = null)
    {
        $this->string = $string;
    }


    /**
     * @param null $start
     * @param null $end
     * @return Parser
     */
    public function inner($start = null, $end = null, $reverse = false)
    {
        if (is_null($this->string)) {
            return $this;
        }

        if (is_null($start)) {
            $startOffset = $this->offset;
        } else {
            $startOffset = strpos($this->string, (string)$start, $this->offset);
            if (false === $startOffset) {
                return new static();
            }
            $startOffset += strlen($start);
        }

        if (is_null($end)) {
            $endOffset = strlen($this->string);
        } else {
            if ($reverse) {
                $endOffset = strrpos($this->string, (string)$end, $startOffset);
            }
            else {
                $endOffset = strpos($this->string, (string)$end, $startOffset);
            }
            if (false === $endOffset) {
                return new static();
            }
        }

        $this->offset = $endOffset + strlen($end);
        return new static(substr($this->string, $startOffset, $endOffset - $startOffset));
    }


    public function getOffset()
    {
        return $this->offset;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function __toString()
    {
        return (string)$this->string;
    }


    public function isEmpty()
    {
        return null === $this->string;
    }

    /**
     * @param null $start
     * @param null $end
     * @return Parser[]|Traversable
     */
    public function innerAll($start = null, $end = null)
    {
        return new \Yaoi\String\ParserIterator($this, $start, $end);
    }


    public function toCamelCase($delimiter = '_')
    {
        return implode('', array_map('ucfirst', explode($delimiter, $this->string)));
    }

    public function fromCamelCase($delimiter = '_')
    {
        return strtolower(ltrim(preg_replace('/([A-Z])/', $delimiter . '$1', $this->string), $delimiter));
    }

    public function starts($needle)
    {
        return substr($this->string, 0, strlen($needle)) === (string)$needle;
    }

    public function ends($needle)
    {
        return substr($this->string, -strlen($needle)) === (string)$needle;
    }

}