<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Command\Application;

class TestApplication extends Application
{


    static function setUpCommands()
    {
        $commandDefinitions = array();
        $commandDefinitions []= TestCommandOne::definition();
        $commandDefinitions []= TestCommandWithVersion::definition();
        return $commandDefinitions;
    }

}