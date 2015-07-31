<?php
/**
 * Created by PhpStorm.
 * User: vpoturaev
 * Date: 7/29/15
 * Time: 11:55
 */

namespace PHPUnit\Sql;


use Yaoi\Sql\Expression;
use Yaoi\Test\PHPUnit\TestCase;

class BasicTest extends TestCase
{

    /**
     * Appends expression without adding extra space
     *
     * @throws \Yaoi\Database\Exception
     * @see Expression::appendExpr
     */
    public function testAppend() {
        $this->assertSame(
            'start-middle-end',
            Expression::create('start')->appendExpr('-mid?l?', 'd', 'e')->appendExpr('-e?d', 'n')->build()
        );
    }


    public function testQuote() {
        $this->assertSame('', Expression::create('m?ddle', 1)->build());
    }

}