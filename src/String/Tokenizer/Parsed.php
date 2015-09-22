<?php
namespace Yaoi\String\Tokenizer;


use Yaoi\String\Expression;

class Parsed
{
    private static $sequenceId = 0;

    public $id;

    public function __construct() {
        $this->id = self::$sequenceId++;
    }

    /** @var  Parsed */
    public $parent;

    /** @var  Bracket */
    public $bracket;

    public $tokens = array();

    public $bindKeyPrefix = ':B';
    public $bindKeyPostfix = ':';

    public function getExpression($strip = array(), $keep = array())
    {
        $binds = array();
        $index = 0;
        $strip = array_flip($strip);
        $keep = array_flip($keep);

        return $this->makeExpression($strip, $keep, $binds, $index);
    }

    private function makeExpression($strip, $keep, &$binds, &$index) {
        $statement = '';

        foreach ($this->tokens as $token) {
            if ($token instanceof Token) {
                if (isset($strip[$token->start])) {
                    continue;
                }
                elseif (isset($keep[$token->start])) {
                    $statement .= $token->start . $token->escapedContent . $token->end;
                }
                else {
                    $key = $this->bindKeyPrefix . $index . $this->bindKeyPostfix;
                    $statement .= $key;
                    $binds [$key]= $token;
                    ++$index;
                }
            }
            elseif ($token instanceof Parsed) {
                $token->bindKeyPrefix = $this->bindKeyPrefix;
                $token->bindKeyPostfix = $this->bindKeyPostfix;
                if (isset($strip[$token->bracket->start])) {
                    continue;
                }
                elseif (isset($keep[$token->bracket->start])) {
                    $statement .= $token->bracket->start . $token->makeExpression($strip, $keep, $binds, $index)->getStatement() . $token->bracket->end;
                }
                else {
                    $key = $this->bindKeyPrefix . $index . $this->bindKeyPostfix;
                    $statement .= $key;
                    $binds [$key]= $token;
                    ++$index;
                }
            }
            else {
                $statement .= $token;
            }
        }

        $expression = new Expression($statement, $binds);
        $expression->setNamedPrefix('');
        return $expression;
    }

}