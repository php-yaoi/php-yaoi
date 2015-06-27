<?php

namespace Yaoi\View;

use Yaoi\IsEmpty;

interface Renderer extends IsEmpty
{
    public function render();

    public function __toString();
}
