<?php

namespace Yaoi\Cli\View;

use Yaoi\Cli\Console;
use Yaoi\Io\Content\Renderer;
use Yaoi\View\Hardcoded;

class Text extends Hardcoded implements Renderer
{

    /** @var  \Yaoi\Io\Content\Text */
    public $text;
    public function __construct(\Yaoi\Io\Content\Text $item)
    {
        $this->text = $item;
    }

    /**
     * @return static[]
     */
    public function lines() {
        $lines = explode("\n", str_replace("\r\n", "\n", $this->text->value));
        if (count($lines) > 1) {
            $result = array();
            foreach ($lines as $line) {
                $result [] = new static($this->text->create($line));
            }
        } else {
            $result = array($this);
        }
        return $result;
    }

    public function strPad($length, $padString = ' ') {
        $textLength = $this->text->length();
        if ($textLength < $length) {
            $this->text->value .= str_pad('', $length - $textLength, $padString);
        }
        return $this;
    }



    public function render()
    {
        $console = Console::getInstance();
        $value = $this->text->value;

        switch ($this->text->type) {
            case \Yaoi\Io\Content\Text::ERROR:
                $console->set(Console::FG_WHITE, Console::BG_RED);
                break;

            case \Yaoi\Io\Content\Text::INFO:
                $console->set(Console::FG_GREEN, Console::BOLD);
                break;

            case \Yaoi\Io\Content\Text::SUCCESS:
                $console->set(Console::FG_BLACK, Console::BG_GREEN);
                break;

            case \Yaoi\Io\Content\Text::HEADING:
                $console->set(Console::FG_CYAN, Console::BOLD);
                break;

            case \Yaoi\Io\Content\Text::TEXT:
                $console->set();
                break;

        }

        $console->printF($value);
        $console->set();
    }


}