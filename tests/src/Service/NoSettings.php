<?php
namespace YaoiTests\Service;

use Yaoi\Service;

class NoSettings extends BasicExposed
{
    protected static function getSettingsClassName() {
        return null;
    }
}