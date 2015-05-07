<?php

class String_Parser extends Base_Class implements Is_Empty {
    private $string;

    private $offset = 0;

    public function __construct($string = null) {
        $this->string = $string;
    }


    /**
     * @param null $start
     * @param null $end
     * @return String_Parser
     */
    public function inner($start = null, $end = null) {
        if (is_null($this->string)) {
            return $this;
        }

        if (is_null($start)) {
            $startOffset = $this->offset;
        }
        else {
            $startOffset = strpos($this->string, (string)$start, $this->offset);
            if (false === $startOffset) {
                return new static();
            }
            $startOffset += strlen($start);
        }

        if (is_null($end)) {
            $endOffset = strlen($this->string);
        }
        else {
            $endOffset = strpos($this->string, (string)$end, $startOffset);
            if (false === $endOffset) {
                return new static();
            }
        }

        $this->offset = $endOffset + strlen($end);
        return new static(substr($this->string, $startOffset, $endOffset - $startOffset));
    }



    public function getOffset() {
        return $this->offset;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function __toString() {
        return (string)$this->string;
    }


    public function isEmpty() {
        return null === $this->string;
    }

    /**
     * @param null $start
     * @param null $end
     * @return String_Parser[]|Traversable
     */
    public function innerAll($start = null, $end = null) {
        return new String_ParserIterator($this, $start, $end);
    }


    public function toCamelCase($delimiter = '_') {
        return implode('', array_map('ucfirst', explode($delimiter, $this->string)));
    }

    public function fromCamelCase($delimiter = '_') {
        return strtolower(ltrim(preg_replace('/([A-Z])/', $delimiter . '$1', $this->string), $delimiter));
    }

    public function starts($needle) {
        return substr($this->string, 0, strlen($needle)) === (string)$needle;
    }

    public function ends($needle) {
        return substr($this->string, -strlen($needle)) === (string)$needle;
    }

}

class String_ParserIterator implements Iterator {

    private $start;
    private $end;
    private $valid;
    /**
     * @var String_Parser
     */
    private $current;
    private $position = -1;
    /**
     * @var String_Parser
     */
    private $parser;
    private $offset;

    public function __construct(String_Parser $parser, $start = null, $end = null) {
        $this->parser = $parser;
        $this->offset = $parser->getOffset();
        $this->start = $start;
        $this->end = $end;
    }

    public function current()
    {
        if (null === $this->current) {
            $this->next();
        }
        return $this->current;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->current = $this->parser->inner($this->start, $this->end);
        if ($this->current->isEmpty()) {
            $this->valid = false;
            $this->position = -1;
        }
        else {
            $this->valid = true;
            ++$this->position;
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->valid;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->parser->setOffset($this->offset);
        $this->position = -1;
        $this->next();
    }
}