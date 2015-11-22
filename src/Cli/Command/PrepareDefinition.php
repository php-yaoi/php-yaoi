<?php

namespace Yaoi\Cli\Command;


use Yaoi\BaseClass;
use Yaoi\Cli\Exception;
use Yaoi\Cli\Option;

class PrepareDefinition extends BaseClass
{
    /** @var Option[] */
    public $requiredArguments = array();

    /** @var Option[] $optionalArguments */
    public $optionalArguments = array();

    /** @var Option[] $byShortName */
    public $byShortName = array();

    /** @var Option[] $byName */
    public $byName = array();

    /** @var Option[] $requiredOptions */
    public $requiredOptions = array();


    public function __construct($options)
    {
        /** @var Option $hasVariadicArgument */
        $hasVariadicArgument = null;

        foreach ($options as $option) {
            $option = Option::cast($option);
            if ($option->isUnnamed) {
                if ($hasVariadicArgument) {
                    throw new Exception('Non-tailing variadic argument ' . $hasVariadicArgument->getUsage(),
                        Exception::NON_TAILING_VARIADIC_ARGUMENT);
                }

                if ($option->isVariadic) {
                    $hasVariadicArgument = $option;
                }

                if ($option->isRequired) {
                    if ($this->optionalArguments) {
                        throw new Exception('Non-tailing optional argument', Exception::NON_TAILING_OPTIONAL_ARGUMENT);
                    }
                    $this->requiredArguments [] = $option;
                } else {
                    $this->optionalArguments [] = $option;
                }
                continue;
            }

            if ($option->isRequired) {
                $this->requiredOptions[$option->name] = $option;
            }

            if ($option->shortName) {
                $this->byShortName[$option->shortName] = $option;
            }

            $this->byName[$option->getName()] = $option;
        }

    }

}