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
     * @param bool $reverse
     * @return Parser
     */
    public function inner($start = null, $end = null, $reverse = false, $ignoreCase = false)
    {
        if (null === $this->string) {
            return $this;
        }

        if ($start === null) {
            $startOffset = $this->offset;
        } else {
            $startOffset = Utils::strPos($this->string, $start, $this->offset, false, $ignoreCase);
            if (false === $startOffset) {
                return new static();
            }
            $startOffset += strlen($start);
        }

        if ($end === null) {
            $endOffset = strlen($this->string);
        } else {
            $endOffset = Utils::strPos($this->string, $end, $startOffset, $reverse, $ignoreCase);
            if (false === $endOffset) {
                return new static();
            }
        }

        $this->offset = $endOffset + strlen(Utils::$strPosLastFound);
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

    public function starts($needle, $ignoreCase = false)
    {
        if ($ignoreCase) {
            $string = strtolower($this->string);
            $needle = strtolower($needle);
        }
        else {
            $string = $this->string;
        }
        return substr($string, 0, strlen($needle)) === (string)$needle;
    }

    public function ends($needle, $ignoreCase = false)
    {
        if ($ignoreCase) {
            $string = strtolower($this->string);
            $needle = strtolower($needle);
        } else {
            $string = $this->string;
        }
        return substr($string, -strlen($needle)) === (string)$needle;
    }

    public function contain($needle, $ignoreCase = false)
    {
        return Utils::strPos($this->string, $needle, 0, false, $ignoreCase) !== false;
    }

    public function explode($delimiter, $limit = 10000)
    {
        return explode($delimiter, $this->string, $limit);
    }

}