<?php

namespace Yaoi\Command\Web;


use Yaoi\Cli\Option;
use Yaoi\Command;
use Yaoi\Command\RequestMapperContract;
use Yaoi\Io\Request;
use Yaoi\String\Utils;

class RequestMapperSimple implements RequestMapperContract
{

    private $request;
    public function __construct(Request $request) {
        $this->request = $request;
    }


    /**
     * @param Option[] $commandOptions
     * @return \stdClass
     * @throws Command\Exception
     */
    public function readOptions(array $commandOptions)
    {
        $commandState = new \stdClass();

        foreach ($commandOptions as $option) {
            $publicName = $this->getPublicName($option->name);
            if (false !== ($value = $this->request->request($publicName, false)
                )
            ) {

                if (Option::TYPE_ENUM === $option->type) {
                    $valueFound = false;
                    foreach ($option->enumValues as $enumName => $enumValue) {
                        $enumPublicName = $this->getPublicName($enumName);
                        if ($enumPublicName === $value) {
                            $valueFound = true;
                            $value = $enumValue;
                            break;
                        }
                    }
                    if (!$valueFound) {
                        throw new Command\Exception('Invalid value for ' . $publicName, Command\Exception::INVALID_VALUE);
                    }
                }

                if (!$value && Option::TYPE_VALUE === $option->type) {
                    throw new Command\Exception('Value required for ' . $publicName, Command\Exception::VALUE_REQUIRED);
                }

                if ($option->isVariadic) {
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                }

                if (Option::TYPE_BOOL === $option->type) {
                    $value = (bool)$value;
                }

                $commandState->{$option->name} = $value;
            } else {
                if ($option->isRequired) {
                    throw new Command\Exception('Option ' . $publicName . ' required', Command\Exception::OPTION_REQUIRED);
                }
            }
        }

        return $commandState;
    }

    public static function getPublicName($name)
    {
        return Utils::fromCamelCase($name, '_');
    }


}