<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Command;
use Yaoi\Command\Definition;

class TestCommandNestedInner extends Command
{
    /** @var Command */
    public $do;

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->do = Command\Option::create()
            ->addToEnum(TestCommandWithSuccessMessage::definition())
            ->addToEnum(TestCommandWithVersion::definition());
    }

    public function performAction()
    {
        $this->do->performAction();
    }

}