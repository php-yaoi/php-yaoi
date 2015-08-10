<?php

namespace PHPUnit\String;

use Yaoi\String\Exception;
use Yaoi\String\Formatter;
use Yaoi\String\Quoter\DoubleQuote;
use Yaoi\String\Quoter\Raw;
use Yaoi\Test\PHPUnit\TestCase;

class FormatterTest extends TestCase
{
    /**
     * You can use unnamed placeholders in statement with '?' mark.
     * Binds can be listed as constructor arguments or array.
     *
     * @throws \Yaoi\String\Exception
     * @see Formatter::__construct
     */
    public function testUnnamedPlaceholders() {
        $expression = Formatter::create('?, ?, ?, ?', 1, 2, 'three', 'a "four"');
        $this->assertSame('1, 2, three, a "four"', $expression->build(new Raw()));

        $expression = Formatter::create('?, ?, ?, ?', array(1, 2, 'three', 'a "four"'));
        $this->assertSame('1, 2, three, a "four"', $expression->build(new Raw()));

        $expression = Formatter::create('? belong to ?', array('phone', 'car', 'camera'), 'Me');
        $this->assertSame('phone, car, camera belong to Me', $expression->build(new Raw()));
    }

    /**
     * Count of unnamed placeholders should match count of binds, otherwise exception is thrown.
     *
     * @expectedException \Yaoi\String\Exception
     * @expectedExceptionCode \Yaoi\String\Exception::PLACEHOLDER_REDUNDANT
     * @throws \Yaoi\String\Exception
     * @see Formatter::__construct
     */
    public function testRedundantPlaceholders() {
        Formatter::create('?, ?, ?', 1, 2)->build(new Raw());
    }

    /**
     * Count of unnamed placeholders should match count of binds, otherwise exception is thrown.
     *
     * @expectedException \Yaoi\String\Exception
     * @expectedExceptionCode \Yaoi\String\Exception::PLACEHOLDER_NOT_FOUND
     * @throws \Yaoi\String\Exception
     * @see Formatter::__construct
     */
    public function testPlaceholderNotFound() {
        Formatter::create('?, ?', 1, 2, 3)->build(new Raw());
    }


    /**
     * If you can not use '?' as placeholder, you can use any other string.
     *
     * @throws Exception
     * @see Formatter::setPlaceholder
     * @see Formatter::__construct
     */
    public function testCustomPlaceholder() {
        $this->assertSame(
            'Would you prefer cake or ice-cream?',
            Formatter::create('Would you prefer @@ or @@?', 'cake', 'ice-cream')
                ->setPlaceholder('@@')
                ->build(new Raw())
        );
    }


    /**
     * You can use named placeholders with ':' prepended to key,
     * only array of binds is allowed for named placeholders
     *
     * @throws \Yaoi\String\Exception
     * @see Formatter::__construct
     */
    public function testNamedPlaceholders() {
        $expression = new Formatter(":one, :two, :three, :four, :five, :one again",
            array('one' => 1, 'two' => 2, 'three' => 'three', 'four' => 'a "four"'));
        $this->assertSame('1, 2, three, a "four", :five, 1 again', $expression->build(new Raw()));
    }

    /**
     * You can send statement and binds in array.
     * This can be helpful for calling parent constructor with func_get_args() in subclass.
     *
     * @throws \Yaoi\String\Exception
     * @see Formatter::__construct
     */
    public function testTroughPut() {
        $arguments = array('the ? and ? statement', 'small', 'safe');
        $this->assertSame('the small and safe statement', Formatter::create($arguments)->build(new Raw()));
    }

    /**
     * You don't need quoter if you don't have binds, and actually you don't need formatter in this case :).
     *
     * @throws Exception
     * @see Formatter::__construct
     * @see Formatter::__toString
     */
    public function testNoBinds() {
        $this->assertSame('no binds', Formatter::create('no binds')->build());
        $this->assertSame('no binds', (string)Formatter::create('no binds'));
    }


    /**
     * To have automatic cast to string you need to specify default quoter.
     *
     * @throws \Yaoi\String\Exception
     * @see Formatter::setQuoter
     */
    public function testSetQuoter() {
        $expression = Formatter::create('? AND ?', 'You', 'Me')->setQuoter(new Raw());
        $this->assertSame('You AND Me', (string)$expression);
        $this->assertSame('You AND Me', $expression->build());
        $this->assertSame('"You" AND "Me"', $expression->build(new DoubleQuote()));
        $this->assertSame('You AND Me', $expression->build());
        $expression->setQuoter(new DoubleQuote());
        $this->assertSame('"You" AND "Me"', $expression->build());
    }

    /**
     * Build with binds and no quoter will fail with exception
     *
     * @expectedException \Yaoi\String\Exception
     * @expectedExceptionCode \Yaoi\String\Exception::MISSING_QUOTER
     * @throws \Yaoi\String\Exception
     * @see Formatter::setQuoter
     * @see Formatter::build
     */
    public function testMissingQuoter() {
        Formatter::create('? AND ?', 1, 2)->build();
    }

    /**
     * String cast with binds and no quoter will result string with error message.
     * Uncaught exception during build will be converted to error string result (with code and message).
     *
     * @see Formatter::setQuoter
     * @see Formatter::__toString
     */
    public function testMissingQuotesToString() {
        $this->assertSame('#ERROR: (2) Missing quoter', (string)Formatter::create('? AND ?', 1, 2));
    }

    /**
     * You can build same statement against different binds.
     *
     * @see Formatter::setBinds
     */
    public function testSetBinds() {
        $expression = Formatter::create('? AND ?', 'You', 'Me')->setQuoter(new Raw());
        $this->assertSame('You AND Me', (string)$expression);

        $expression->setBinds('Me', 'You');
        $this->assertSame('Me AND You', (string)$expression);

        $this->assertSame('Me AND Me', (string)$expression->setBinds(array('Me', 'Me')));
    }

    public function testDoubleQuote() {
        $expression = new Formatter('?, ?, ?, ?', 1, 2, 'three', 'a "four"');
        $this->assertSame('"1", "2", "three", "a \"four\""', $expression->build(new DoubleQuote()));
    }

}