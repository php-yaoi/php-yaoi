<?php

namespace YaoiTests\PHPUnit\Database\Definition;


use Yaoi\Database\Definition\Column;
use YaoiTests\PHPUnit\Base\Test;

class ColumnTest extends Test
{
    public function testNullCast()
    {
        $this->assertSame(null, Column::castField(null, Column::INTEGER));
        $this->assertSame(0, Column::castField(null, Column::INTEGER | Column::NOT_NULL));
        $this->assertSame('', Column::castField(null, Column::STRING | Column::NOT_NULL));
    }
}