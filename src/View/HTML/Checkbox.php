<?php

namespace Yaoi\View\HTML;

class Checkbox extends Input
{
    public function render()
    {
        $this->setAttribute('type', 'checkbox');
        if ($this->value) {
            $this->setAttribute('checked', 'checked');
        } else {
            $this->setAttribute('checked');
        }
        return parent::render();
    }
} 