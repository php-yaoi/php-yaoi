<?php

namespace Yaoi\Io\Content;


use Yaoi\BaseClass;

class ItemList extends BaseClass implements Element
{
    public $items = array();

    public function addItem($item)
    {
        $this->items[] = $item;
        return $this;
    }

}