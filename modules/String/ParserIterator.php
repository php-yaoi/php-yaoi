<?php
namespace Yaoi\String;

use Iterator;
use Yaoi\String\Parser;

class ParserIterator implements Iterator
{

    private $start;
    private $end;
    private $valid;
    /**
     * @var Parser
     */
    private $current;
    private $position = -1;
    /**
     * @var Parser
     */
    private $parser;
    private $offset;

    public function __construct(Parser $parser, $start = null, $end = null)
    {
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
        } else {
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