<?php

namespace Yaoi\String\Lexer;


use Yaoi\BaseClass;
use Yaoi\String\Expression;

class Renderer extends BaseClass
{
    private $keep = array();
    private $keepBoundaries = array();
    private $strip = array();

    private $bindKeyPrefix = ':B';
    private $bindKeyPostfix = ':';

    public function __construct($keep = array(), $keepBoundaries = array(), $strip = array()) {
        $this->keep = array_flip($keep);
        $this->keepBoundaries = array_flip($keepBoundaries);
        $this->strip = array_flip($strip);
    }

    public function setBindKey($prefix = ':B', $postfix = ':') {
        $this->bindKeyPrefix = $prefix;
        $this->bindKeyPostfix = $postfix;
        return $this;
    }

    public function strip()
    {
        $this->strip = array_flip(func_get_args());
        return $this;
    }

    public function keep()
    {
        $this->keep = array_flip(func_get_args());
        return $this;
    }

    public function keepBoundaries() {
        $this->keepBoundaries = array_flip(func_get_args());
        return $this;
    }

    public function getExpression(Parsed $parsed)
    {
        $binds = array();
        $index = 0;

        return $this->makeExpression($parsed, $binds, $index);
    }

    private function makeExpression(Parsed $parsed, &$binds, &$index) {
        $statement = '';

        foreach ($parsed->tokens as $token) {
            if ($token instanceof Token) {
                if (isset($this->strip[$token->start])) {
                    continue;
                }
                elseif (isset($this->keep[$token->start])) {
                    $statement .= $token->start . $token->escapedContent . $token->end;
                }
                elseif (isset($this->keepBoundaries[$token->start])) {
                    $key = $this->bindKeyPrefix . $index . $this->bindKeyPostfix;
                    $statement .= $token->start . $key . $token->end;
                    $binds [$key]= $token;
                    ++$index;
                }
                else {
                    $key = $this->bindKeyPrefix . $index . $this->bindKeyPostfix;
                    $statement .= $key;
                    $binds [$key]= $token;
                    ++$index;
                }
            }
            elseif ($token instanceof Parsed) {
                if (isset($this->strip[$token->bracket->start])) {
                    continue;
                }
                elseif (isset($this->keep[$token->bracket->start])) {
                    $statement .= $token->bracket->start
                        . $this->makeExpression($token, $binds, $index)->getStatement()
                        . $token->bracket->end;
                }
                elseif (isset($this->keepBoundaries[$token->bracket->start])) {
                    $key = $this->bindKeyPrefix . $index . $this->bindKeyPostfix;
                    $statement .= $token->bracket->start . $key . $token->bracket->end;
                    $binds [$key]= $token;
                    ++$index;
                }
                else {
                    $key = $this->bindKeyPrefix . $index . $this->bindKeyPostfix;
                    $statement .= $key;
                    $binds [$key]= $token;
                    ++$index;
                }
            }
            elseif ($token instanceof Delimiter) {
                $statement .= $token->start;
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