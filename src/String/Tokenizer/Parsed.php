<?php
namespace Yaoi\String\Tokenizer;


use Yaoi\String\Formatter;

class Parsed
{
    public $tokens = array();

    public function getExpression($strip = array(), $keep = array()) {
        $index = 0;
        $statement = '';
        $binds = array();

        $strip = array_flip($strip);
        $keep = array_flip($keep);

        foreach ($this->tokens as $token) {
            if ($token instanceof Token) {
                if (isset($strip[$token->start])) {
                    continue;
                }
                elseif (isset($keep[$token->start])) {
                    $statement .= $token->start . $token->original . $token->end;
                }
                else {
                    $statement .= ':B' . $index;
                    $binds ['B' . $index]= $token;
                    ++$index;
                }
            }
            else {
                $statement .= $token;
            }
        }

        $expression = new Formatter($statement, $binds);
        return $expression;
    }

}