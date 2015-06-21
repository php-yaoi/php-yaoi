<?php

namespace Yaoi\Storage;

use Yaoi\Storage;

class PhpVar extends Storage
{
    public function __construct()
    {
        $settings = self::createSettings();
        $settings->driverClassName = Driver\PhpVar::className();
        parent::__construct($settings);
    }
}