<?php
use Yaoi\Database;
use Yaoi\Sql\Expression;
use Yaoi\Test\PHPUnit\TestCase;



class SqlExpressionTest extends TestCase {
    public function testExpression() {
        $s = new Expression('test ? ? ?', 1, 2, 3);
        $driver = Database::getInstance('test_mysqli')->getDriver();

        $this->assertSame('test 1 2 3', $s->build($driver));
    }


    public function testEmpty() {
        $s = new Expression();
        $this->assertSame(true, $s->isEmpty());
        $this->assertSame('', $s->build());
    }

    /**
     * @expectedException     \Yaoi\Sql\Exception
     * @expectedExceptionCode \Yaoi\Sql\Exception::STATEMENT_REQUIRED
     */
    public function testCreateFromArgumentsStatementRequired() {
        Expression::createFromFuncArguments(array());
    }


    public function testCreateFromArgumentsProxy() {
        $ex = new Expression('test');
        $this->assertSame($ex, Expression::createFromFuncArguments(array($ex)));
    }

    public function testCreateFromArgumentsClosure() {
        $ex = new Expression('test');
        $this->assertSame($ex, Expression::createFromFuncArguments(array(
            function() use ($ex) {
                return $ex;
            }
        )));
    }


    /**
     * @expectedException     \Yaoi\Sql\Exception
     * @expectedExceptionCode \Yaoi\Sql\Exception::CLOSURE_MISTYPE
     */
    public function testCreateFromArgumentsBadClosure() {
        Expression::createFromFuncArguments(array(
            function() {
                return 'not a Sql_Expression instance';
            }
        ));
    }


    public function testXor() {
        $ex = new Expression('1');
        $ex->xorExpr('2');
        $this->assertSame('1 XOR 2', $ex->build());
    }


    /**
     * @expectedException     \Yaoi\Database\Exception
     * @expectedExceptionCode \Yaoi\Database\Exception::PLACEHOLDER_NOT_FOUND
     */
    public function testPlaceholderNotFound() {
        $ex = new Expression('1 = 1', 1);
        $ex->build(Database::getInstance()->getDriver());
    }

    /**
     * @expectedException     \Yaoi\Database\Exception
     * @expectedExceptionCode \Yaoi\Database\Exception::PLACEHOLDER_REDUNDANT
     */
    public function testPlaceholderRedundant() {
        $ex = new Expression('1 = ? AND 2 = ?', 1);
        $ex->build(Database::getInstance()->getDriver());
    }


    public function testEnable() {
        $ex = new Expression('1');
        $or = new Expression('2');

        $ex->orExpr($or);
        $this->assertSame('1 OR 2', $ex->build());

        $or->disable();
        $this->assertSame('1', $ex->build());

        $or->enable();
        $this->assertSame('1 OR 2', $ex->build());
    }


} 