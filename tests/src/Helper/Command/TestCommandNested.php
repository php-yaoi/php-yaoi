<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Command;
use Yaoi\Command\Definition;

/**
 * Class TestCommandNested
 * @package YaoiTests
 * @internal
 */
class TestCommandNested extends Command
{

    /** @var Command */
    public $action;

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->action = Command\Option::create()
            ->setIsUnnamed()
            ->addToEnum(TestCommandOne::definition())
            ->addToEnum(TestCommandWithRequiredOption::definition());
    }

    public function performAction()
    {
        return $this->action->performAction();
    }

}