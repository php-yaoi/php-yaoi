<?php

namespace Yaoi\Service;


interface Contract
{
    /**
     * @return \Yaoi\Service\Settings
     */
    public static function getSettingsClassName();
}