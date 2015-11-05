<?php

namespace Yaoi\Cli\View;

use Yaoi\Cli\Console;
use Yaoi\View\Hardcoded;
use Yaoi\View\Semantic\Renderer;
use Yaoi\View\Semantic\Semantic;

class Text extends Hardcoded implements Renderer
{

    /** @var  \Yaoi\View\Semantic\Text */
    private $text;
    public function __construct(\Yaoi\View\Semantic\Text $item)
    {
        $this->text = $item;
    }


    public function render()
    {
        $console = Console::getInstance();
        if ($this->text->type === \Yaoi\View\Semantic\Text::ERROR) {
            $console->set(Console::FG_RED);
        }
        $console->printF($this->text);
    }


}