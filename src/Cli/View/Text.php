<?php

namespace Yaoi\Cli\View;

use Yaoi\Cli\Console;
use Yaoi\View\Hardcoded;
use Yaoi\View\Semantic\Renderer;

class Text extends Hardcoded implements Renderer
{

    /** @var  \Yaoi\View\Semantic\Text */
    public $text;
    public function __construct(\Yaoi\View\Semantic\Text $item)
    {
        $this->text = $item;
    }

    /**
     * @return static[]
     */
    public function lines() {
        $lines = explode("\n", str_replace("\r\n", "\n", $this->text->value));
        $result = array();
        foreach ($lines as $line) {
            $result []= new static($this->text->create($line));
        }
        return $result;
    }

    public function strPad($length, $padString = ' ') {
        if (strlen($this->text->value) < $length) {
            $this->text->value = str_pad($this->text->value, $length, $padString);
        }
        return $this;
    }



    public function render()
    {
        $console = Console::getInstance();
        $value = $this->text->value;

        switch ($this->text->type) {
            case \Yaoi\View\Semantic\Text::ERROR:
                $value = ' ' . $value . ' ';
                $console->set(Console::FG_WHITE, Console::BG_RED);
                break;

            case \Yaoi\View\Semantic\Text::INFO:
                $console->set(Console::FG_GREEN, Console::BOLD);
                break;

            case \Yaoi\View\Semantic\Text::SUCCESS:
                $value = ' ' . $value . ' ';
                $console->set(Console::FG_BLACK, Console::BG_GREEN);
                break;

            case \Yaoi\View\Semantic\Text::HEADING:
                $console->set(Console::FG_CYAN, Console::BOLD);
                break;

            case \Yaoi\View\Semantic\Text::TEXT:
                $console->set();
                break;

        }

        $console->printF($value);
        $console->set();
    }


}