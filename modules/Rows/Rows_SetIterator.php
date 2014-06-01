<?php

class Rows_SetIterator extends RecursiveArrayIterator {
    public function hasChildren() {
        return is_array($this->current()) || $this->current() instanceof Iterator;
    }
} 