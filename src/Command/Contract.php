<?php

namespace Yaoi\Command;


interface Contract
{
    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $options static|\stdClass
     */
    static function setUpDefinition(Definition $definition, $options);
    public function performAction();
    public function setRunner(Runner $runner);

}