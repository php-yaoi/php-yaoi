<?php

namespace Yaoi\Command\Web;

use Yaoi\Command\Option;
use Yaoi\Command;
use Yaoi\Command\RequestMapperContract;
use Yaoi\Io\Request;
use Yaoi\String\Utils;

class RequestMapperWithPath implements RequestMapperContract
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    private function readUnnamedOption(Option $option)
    {
        if ($option->type === Option::TYPE_ENUM) {
            $option->setEnumMapper($this->unnamedMapper);
        }

        $value = array();

        while (false !== $item = $this->getUnnamed()) {
            $item = $option->validateFilterValue($item);

            if (!$option->isVariadic) {
                $value = $item;
                break;
            } else {
                $value [] = $item;
            }
        }

        if (!$value) {
            $value = false;
        }

        return $value;
    }


    private function readNamedOption(Option $option)
    {
        $mapper = $this->namedMapper;
        $publicName = $mapper($option->name);
        $value = $this->request->request($publicName, false);

        if ($option->type === Option::TYPE_ENUM) {
            $option->setEnumMapper($mapper);
        }

        $value = $option->validateFilterValue($value);

        if ($option->isVariadic) {
            if (!is_array($value)) {
                $value = array($value);
            }
        }

        return $value;
    }


    private $unnamedMapper;
    private $namedMapper;

    /**
     * @param Option[] $commandOptions
     * @return \stdClass
     * @throws Exception
     */
    public function readOptions(array $commandOptions)
    {
        $commandState = new \stdClass();

        $this->unnamedMapper = function($name){
            return Utils::fromCamelCase($name, '-');
        };

        $this->namedMapper = function($name) {
            return Utils::fromCamelCase($name, '_');
        };

        foreach ($commandOptions as $option) {
            if ($option->isUnnamed) {
                $value = $this->readUnnamedOption($option);
            }
            else {
                $value = $this->readNamedOption($option);
            }

            if (false !== $value) {
                $commandState->{$option->name} = $value;
            }

            else {
                if ($option->isRequired) {
                    throw new Command\Exception('Option ' . $option->name . ' required',
                        Command\Exception::OPTION_REQUIRED);
                }
            }
        }

        return $commandState;

    }


    private $unnamedValues;
    private function getUnnamed() {
        if (null === $this->unnamedValues) {
            $this->unnamedValues = explode('/', trim($this->request->path(), '/'));
        }
        if (!$this->unnamedValues) {
            return false;
        }
        else {
            return array_shift($this->unnamedValues);
        }
    }



    public function makeAnchor($commandState) {

    }
}