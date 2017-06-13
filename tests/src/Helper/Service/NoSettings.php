<?php
namespace YaoiTests\Helper\Service;

class NoSettings extends BasicExposed
{
    protected static function getSettingsClassName() {
        return null;
    }
}