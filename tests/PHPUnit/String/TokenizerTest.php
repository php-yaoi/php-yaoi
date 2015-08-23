<?php

namespace PHPUnit\String;

use Yaoi\String\Tokenizer;
use Yaoi\Test\PHPUnit\TestCase;

class TokenizerTest extends TestCase
{
    public function testTokenize() {
        $tokenizer = new Tokenizer();
        $tokenizer->addQuote("'", "'", array("''" => "'"));
        $tokenizer->addQuote('/*', '*/');
        $tokenizer->addLineStopper('-- ');
        $tokenizer->addLineStopper('#');


        $string = <<<EOD
Hello 'World' of 'John O''Connor'
Welcome! -- line prop 'not parsed' here
Peace...
/* some inline comment */ yup # and a line stopper /* not parsed */
EOD;

        $this->assertSame(array(
            'Hello ',
            array('World', '\''),
            ' of ',
            array('John O\'Connor', '\''),
            "\n" . 'Welcome! ',
            array('line prop \'not parsed\' here', '-- '),
            "\n" . 'Peace...' . "\n",
            array(' some inline comment ', '/*'),
            ' yup ',
            array(' and a line stopper /* not parsed */', '#'),
        ),

            $tokenizer->tokenize($string));
    }


    public function testTokenizeTwo() {
        $string = <<<EOD
SELECT 'one', `ta``ble`.* # hello there!
FROM `ta``ble` -- and here
WHERE a = '1 AND b = 0'
EOD;

        $tokenizer = new Tokenizer();
        $tokenizer
            ->addLineStopper('#')
            ->addLineStopper('--')
            ->addQuote('`', '`', array('``' => '`'))
            ->addQuote("'","'", array("''" => "'"));


        $expected = array(
            'SELECT ',
            array('one', '\'',),
            ', ',
            array('ta`ble', '`',),
            '.* ',
            array(' hello there!', '#',),
            "\n" . 'FROM ',
            array('ta`ble', '`',),
            ' ',
            array(' and here', '--',),
            "\n" . 'WHERE a = ',
            array('1 AND b = 0', '\'',),
        );

        $this->assertSame($expected, $tokenizer->tokenize($string));
    }

    public function testThree() {
        $string = <<<EOD
line 'line' line
EOD;
        $tokenizer = new Tokenizer();
        $tokenizer->addQuote("'", "'");
        $this->assertSame(array('line ', array('line', "'"), ' line'), $tokenizer->tokenize($string));
    }


    /**
     * @expectedException \Yaoi\String\Exception
     * @expectedExceptionCode \Yaoi\String\Exception::MALFORMED
     * @throws \Yaoi\String\Exception
     */
    public function testMalformed() {
        $string = <<<EOD
line 'line line
EOD;
        $tokenizer = new Tokenizer();
        $tokenizer->addQuote("'", "'");
        $this->assertSame(array('line ', array('line', "'"), ' line'), $tokenizer->tokenize($string));
    }

}