<?php
/**
 * Created by PhpStorm.
 * User: vpoturaev
 * Date: 7/29/15
 * Time: 13:20
 */

namespace Yaoi\View\HTML;


use Yaoi\String\Quoter;

class ContentQuoter implements Quoter
{
    public function quote($value)
    {
        return str_replace('<', '&lt;', $value);
    }
}