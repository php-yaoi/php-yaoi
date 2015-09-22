<?php

namespace PHPUnit\String;

use Yaoi\String\Tokenizer;
use Yaoi\Test\PHPUnit\TestCase;

class TokenizerTest extends TestCase
{
    public function testTokenize() {
        $t2 = new Tokenizer\Parser();
        $t2->addQuote("'", "'", array("''" => "'"));
        $t2->addQuote('/*', '*/');
        $t2->addLineStopper('-- ');
        $t2->addLineStopper('#');



        $string = <<<EOD
Hello 'World' of 'John O''Connor'
Welcome! -- line prop 'not parsed' here
Peace...
/* some inline comment */ yup # and a line stopper /* not parsed */
EOD;


        $result = $t2->tokenize($string);
        $this->assertSame('Hello :B0 of :B1
Welcome! :B2
Peace...
:B3 yup :B4', $result->getExpression()->getStatement());

        $this->assertSame('Hello :B0 of :B1
Welcome! :B2
Peace...
/* some inline comment */ yup ', $result->getExpression(array('#', ' --'), array('/*'))->getStatement());
    }


    public function testTokenizeTwo() {
        $string = <<<EOD
SELECT 'one', `ta``ble`.* # hello there!
FROM `ta``ble` -- and here
WHERE a = '1 AND b = 0'
EOD;

        $tokenizer = new Tokenizer\Parser();
        $tokenizer
            ->addLineStopper('#')
            ->addLineStopper('-- ')
            ->addQuote('`', '`', array('``' => '`'))
            ->addQuote("'","'", array("''" => "'"));


        $this->assertSame('SELECT :B0, :B1.* :B2
FROM :B3 :B4
WHERE a = :B5', $tokenizer->tokenize($string)->getExpression()->getStatement());

        $this->assertStringEqualsSpaceless('SELECT :B0, `ta``ble`.*
FROM `ta``ble`
WHERE a = :B1', $tokenizer->tokenize($string)->getExpression(array('#', '-- '), array('`'))->getStatement());

        $expression = $tokenizer->tokenize($string)->getExpression();
        $this->assertSame($string, $expression->build(new Tokenizer\Quoter()));
    }

    public function testThree() {
        $string = <<<EOD
line 'line' line
EOD;
        $tokenizer = new Tokenizer\Parser();
        $tokenizer->addQuote("'", "'");
        $this->assertSame('line :B0 line', $tokenizer->tokenize($string)->getExpression()->getStatement());
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
        $tokenizer = new Tokenizer\Parser();
        $tokenizer->addQuote("'", "'");
        $this->assertSame(array('line ', array('line', "'"), ' line'), $tokenizer->tokenize($string));
    }


    public function testElementary() {
        $tokenizer = new Tokenizer\Parser();
        $tokenizer->addQuote("q", "q", array("eq" => "q", 'nnq' => 'q2'));

        $string = 'q eqnnq q';
        $this->assertSame(":B0", $tokenizer->tokenize($string)->getExpression()->getStatement());
        $this->assertSame("q eqnnq q", $tokenizer->tokenize($string)->getExpression(array(), array('q'))->getStatement());

        /** @var Tokenizer\Token[] $binds */
        $binds = $tokenizer->tokenize($string)->getExpression()->getBinds();
        $this->assertSame(' eqnnq ', $binds['B0']->escapedContent);
        $this->assertSame(' qq2 ', $binds['B0']->unEscapedContent);
    }


    public function testBracketsPre() {
        $tokenizer = new Tokenizer\Parser();
        $tokenizer->addBracket('(', ')');
        $tokenizer->addBracket('[', ']');
        $tokenizer->addBracket('{', '}');
        $tokenizer->addQuote('\'','\'', array('\\\'' => '\''));
        $tokenizer->addQuote('"', '"', array('\\"' => '"'));

        $string = <<<'PHP'
if (1 2 3,'''', 'aa') { $a } ;
PHP;
        $this->assertSame('if :B0 :B1 ;', $tokenizer->tokenize($string)->getExpression()->getStatement());
        $this->assertSame('if :B0 { $a } ;', $tokenizer->tokenize($string)->getExpression(array(), array('{'))->getStatement());
        $this->assertSame("if (1 2 3,'''', 'aa') :B0 ;", $tokenizer->tokenize($string)->getExpression(array(), array('(', "'"))->getStatement());
        $this->assertSame("if (1 2 3,:B0:B1, :B2) :B3 ;", $tokenizer->tokenize($string)->getExpression(array(), array('('))->getStatement());
    }

    public function testBrackets() {
        $tokenizer = new Tokenizer\Parser();
        $tokenizer->addBracket('(', ')');
        $tokenizer->addBracket('[', ']');
        $tokenizer->addBracket('{', '}');
        $tokenizer->addQuote('\'','\'', array('\\\'' => '\''));
        $tokenizer->addQuote('"', '"', array('\\"' => '"'));

        $string = <<<'PHP'
if ('a' === "'a'") {
    array('c') == ['c'];
    $fff = 'O\'Lolo';
};
PHP;
        $this->assertSame('if :B0 :B1;', $tokenizer->tokenize($string)->getExpression()->getStatement());
        $this->assertSame('if (:B0 === :B1) :B2;', $tokenizer->tokenize($string)->getExpression(array(), array('('))->getStatement());
    }


    public function testOneLetter() {
        $string = "'a', 'b', 'c'";
        $tokenizer = new Tokenizer\Parser();
        $tokenizer->addQuote("'", "'");
        $this->assertSame(':B0, :B1, :B2', $tokenizer->tokenize($string)->getExpression()->getStatement());
    }
}