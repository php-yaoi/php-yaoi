<?php

namespace YaoiTests\Service;
use Yaoi\Service;

class BasicExposed extends Service
{
    protected static function getSettingsClassName()
    {
        return \Yaoi\Service\Settings::className();
    }

    public static function settings($settings) {
        return parent::settings($settings);
    }
}