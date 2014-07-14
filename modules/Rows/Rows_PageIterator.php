<?php

class Rows_PageIterator implements Iterator {
    private $iterators;
    /**
     * @var Iterator
     */
    private $currentIterator;

    function __construct(Iterator $iterators)
    {
        //echo '_';
        $this->iterators = $iterators;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        //echo 'c';
        return $this->currentIterator->current();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        //echo 'n';
        $this->currentIterator->next();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        //echo 'k';
        return $this->currentIterator->key();
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
        //echo 'v';
        $v = $this->currentIterator->valid();
        if (!$v) {
            do {
                $this->iterators->next();
                if ($this->iterators->valid()) {
                    $this->currentIterator = $this->iterators->current();
                    $this->currentIterator->rewind();
                }
            }
            while ($this->iterators->valid() && !$this->currentIterator->valid());

            if (!$this->currentIterator->valid()) {
                return false;
            }
            else {
                return true;
            }
        }

        return $v;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        //echo 'r';
        $this->iterators->rewind();
        if (!$this->iterators->valid()) {
            $this->currentIterator = new ArrayIterator(array());
        }
        else {
            $this->currentIterator = $this->iterators->current();
        }
        $this->currentIterator->rewind();
    }

}