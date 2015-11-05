<?php
/**
 * Created by PhpStorm.
 * User: vearutop
 * Date: 06.11.2015
 * Time: 3:11
 */

namespace Yaoi\View\Semantic;


class Text
{
    const SUCCESS = 'success';
    const INFO = 'info';
    const ERROR = 'error';
    const NEUTRAL = 'neutral';

    public $value;
    public $type;

    public function __construct($text, $type = self::NEUTRAL)
    {
        $this->value = $text;
        $this->type = $type;
    }

}