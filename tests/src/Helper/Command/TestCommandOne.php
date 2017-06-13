<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Cli\Option;
use Yaoi\Command;
use Yaoi\Command\Definition;

class TestCommandOne extends Command
{
    public $action;
    public $argumentA;
    public $argumentB;
    public $optionC;
    public $optionD;
    public $someEnum;

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $options static|\stdClass
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        $definition->description = 'This is a command one for doing nothing';
        $definition->name = 'the-first';

        $options->action = Option::create()
            ->setIsUnnamed()
            ->setIsRequired()
            ->setEnum('get', 'delete', 'create')
            ->setDescription('Main action');

        $options->argumentA = Option::create()
            ->setDescription('Bee description follows')
            ->setIsUnnamed();

        $options->argumentB = Option::create()
            ->setIsVariadic()
            ->setDescription('This is a variadic argument')
            ->setIsUnnamed();

        $options->optionC = Option::create()
            ->setDescription('Some option for the C');

        $options->optionD = Option::create()
            ->setIsRequired()
            ->setShortName('d')
            ->setType()
            ->setIsVariadic()
            ->setDescription('Short name option with required value');

        $options->someEnum = Option::create()->setIsRequired()
            ->setEnum('one', 'two', 'three')
            ->setDescription('Enumerated option to set up something');
    }

    public function performAction()
    {
        //var_dump($this->someEnum);
        //$this->response->success('Well done!');
        return $this->someEnum;
    }

}