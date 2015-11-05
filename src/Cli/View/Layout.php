<?php

namespace Yaoi\Cli\View;

use Yaoi\View\Renderer;
use Yaoi\View\Semantic\Error;
use Yaoi\View\Semantic\Semantic;
use Yaoi\View\Semantic\Rows;
use Yaoi\View\Semantic\Text;
use Yaoi\View\Stack;

class Layout extends Stack
{

    public function push(Renderer $element)
    {
        $element->render();
        return $this;
    }

    public function pushData(Semantic $semantic) {
        switch (true) {
            case $semantic instanceof Rows:
                $this->renderRows($semantic);
                break;

            case $semantic instanceof Text:


        }
    }

    protected function renderRows(Rows $rows) {
        Table::create($rows)->render();
    }

    protected function renderText(Text $text) {
        \Yaoi\Cli\View\Text::create($text)->render();
    }

}