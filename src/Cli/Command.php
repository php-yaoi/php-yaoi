<?php

namespace Yaoi\Cli;


use Yaoi\Command\Exception;
use Yaoi\Request;
use Yaoi\StringVar;

abstract class Command extends \Yaoi\Command
{
    const OPTION_NAME = '--';
    const OPTION_SHORT = '-';
    public function setup(Request $request) {
        $tokens = $request->server()->argv;
        $argc = count($tokens);
        for ($index = 0; $index < $argc; ++$index) {
            $token = new StringVar($tokens[$index]);
            $option = null;


            if ($optionName = $token->afterStarts(self::OPTION_NAME)) {
                if (!isset($this->optionsByName[$optionName])) {
                    throw new Exception('Unknown option "' . self::OPTION_NAME . $optionName . '"', Exception::UNKNOWN_OPTION);
                }
                $option = $this->optionsByName[$optionName];
            }

            elseif ($optionName = $token->afterStarts(self::OPTION_SHORT)) {
                if (!isset($this->optionsByName[$optionName])) {
                    throw new Exception('Unknown option "' . self::OPTION_NAME . $optionName . '"', Exception::UNKNOWN_OPTION);
                }
                $option = $this->optionsByShortName[$optionName];
            }

            if (null === $option) {
                $this->arguments;


            }

        }
    }


    public function __construct() {
        parent::__construct();
        foreach ((array)$this->options as $name => $option) {
            if ($option instanceof UnnamedArgument) {
                $this->arguments []= $option;
            }

            if ($option instanceof Option && $option->shortName) {
                $this->optionsByShortName [$option->shortName]= $option;
            }
        }
    }

    /**
     * @var UnnamedArgument[]
     */
    public $arguments = array();

    /**
     * @var Option[]
     */
    public $optionsByShortName = array();

}