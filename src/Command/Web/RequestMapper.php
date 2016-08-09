<?php

namespace Yaoi\Command\Web;

use Yaoi\Command\Option;
use Yaoi\Command;
use Yaoi\Command\RequestMapperContract;
use Yaoi\Io\Request;
use Yaoi\String\Expression;
use Yaoi\String\Utils;

class RequestMapper implements RequestMapperContract
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->unnamedMapper = function ($name) {
            return Utils::fromCamelCase($name, '-');
        };

        $this->namedMapper = function ($name) {
            return Utils::fromCamelCase($name, '_');
        };
    }


    private function readUnnamedOption(Option $option, Command\State $commandState, Command\State $requestState)
    {
        if ($option->type === Option::TYPE_ENUM) {
            $option->setEnumMapper($this->unnamedMapper);
        }

        $value = array();
        $requestValue = array();

        while (false !== $item = $this->getUnnamed()) {
            $requestValue[] = $item;
            $item = $option->validateFilterValue($item);

            if (!$option->isVariadic) {
                $value = $item;
                $requestValue = $requestValue[0];
                break;
            } else {
                $value[] = $item;
            }
        }

        if ($value) {
            $commandState->{$option->name} = $value;
        }


        if ($requestValue) {
            $requestState->{$option->name} = $requestValue;
        }
    }


    private function readNamedOption(Option $option, Command\State $commandState, Command\State $requestState)
    {
        $publicName = $this->namedMapper->__invoke($option->name);
        $requestValue = $this->request->request($publicName);

        if ($option->type === Option::TYPE_ENUM) {
            $option->setEnumMapper($this->namedMapper);
        }

        $value = $option->validateFilterValue($requestValue);

        if ($option->isVariadic) {
            if (!is_array($value)) {
                $value = array($value);
            }
        }

        if ($requestValue !== null) {
            $requestState->{$option->name} = $requestValue;
            $commandState->{$option->name} = $value;

        }
    }


    /** @var \Closure */
    private $unnamedMapper;
    /** @var \Closure */
    private $namedMapper;

    /**
     * @param Option[] $commandOptions
     * @return \stdClass
     * @throws Command\Exception
     */
    /**
     * @param array $commandOptions
     * @param Command\State $commandState
     * @param Command\State $requestState
     */
    public function readOptions(array $commandOptions, Command\State $commandState, Command\State $requestState)
    {
        foreach ($commandOptions as $option) {
            if ($option->isUnnamed) {
                $this->readUnnamedOption($option, $commandState, $requestState);
            } else {
                $this->readNamedOption($option, $commandState, $requestState);
            }

            /*
            if (false === $value && $option->isRequired) {
                throw new Command\Exception('Option ' . $option->name . ' required',
                    Command\Exception::OPTION_REQUIRED);
            }
            */
        }
    }


    private $unnamedValues;

    private function getUnnamed()
    {
        if (null === $this->unnamedValues) {
            $this->unnamedValues = explode('/', trim($this->request->path(), '/'));
        }
        if (!$this->unnamedValues) {
            return false;
        } else {
            return array_shift($this->unnamedValues);
        }
    }

    /**
     * @param array $properties
     * @return Expression
     */
    public function makeAnchor(array $properties)
    {
        if (!$properties) {
            throw new Command\Exception('Unable to make anchor, no properties');
        }

        $unnamed = array();
        $unnamedTemplate = '';

        $queryTemplate = '';
        $query = array();

        foreach ($properties as $property) {
            /** @var Option $option */
            list($option, $value) = $property;

            if ($option->isUnnamed) {
                if (!$option->isVariadic) {
                    $value = array($value);
                }
                foreach ($value as $item) {
                    $unnamedTemplate .= '/??';
                    $unnamed[] = $this->unnamedMapper->__invoke($item);
                }
            } else {
                $queryTemplate .= '&' . $this->namedMapper->__invoke($option->name) . '=??';
                $query[] = $value;
            }
        }

        $template = $unnamedTemplate;
        $binds = $unnamed;
        if ($queryTemplate) {
            $template .= '?' . substr($queryTemplate, 1);
            $binds = array_merge($binds, $query);
        }

        $expression = new Expression($template, $binds);
        $expression->setPlaceholder('??');
        return $expression;
    }

    public function getExportName(Option $option)
    {
        if ($option->isUnnamed) {
            return $this->unnamedMapper->__invoke($option->name);
        } else {
            return $this->namedMapper->__invoke($option->name);
        }
    }

}