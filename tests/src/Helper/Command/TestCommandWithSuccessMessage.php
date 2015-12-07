<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Command;
use Yaoi\Command\Definition;

class TestCommandWithSuccessMessage extends Command
{
    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $options static|\stdClass
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        $definition->description = 'Test command with success message';
    }

    public function performAction()
    {
        $this->runner->success('Congratulations!');
    }

}