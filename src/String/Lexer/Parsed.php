<?php
namespace Yaoi\String\Lexer;


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

    /** @var  Delimiter */
    public $delimiter;
    /** @var  Delimiter */
    public $delimitedTail;

    public $tokens = array();


    /**
     * @param $by
     * @return Parsed[]
     */
    public function split($by) {
        $result = array();
        $row = array();
        foreach ($this->tokens as $token) {
            if ($token instanceof Delimiter && $by === $token->start) {
                $parsed = new Parsed();
                $parsed->tokens = $row;
                $result []= $parsed;
                $row = array();
            }
            else {
                $row []= $token;
            }
        }
        $parsed = new Parsed();
        $parsed->tokens = $row;
        $result []= $parsed;

        return $result;
    }

}