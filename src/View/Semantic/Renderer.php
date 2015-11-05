<?php

namespace Yaoi\View\Semantic;

interface Renderer extends \Yaoi\View\Renderer
{
    public function __construct(Semantic $item);
}