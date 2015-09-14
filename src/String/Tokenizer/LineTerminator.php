<?php
/**
 * Created by PhpStorm.
 * User: vpoturaev
 * Date: 9/14/15
 * Time: 08:35
 */

namespace Yaoi\String\Tokenizer;


class LineTerminator
{
    public $start;
    public $startLen;

    public function __construct($start) {
        $this->start = $start;
        $this->startLen = strlen($start);
    }

}