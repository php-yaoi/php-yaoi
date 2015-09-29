<?php

namespace YaoiTests\Helper\Service;

use Yaoi\Storage;


require_once __DIR__ . '/TestServiceThree_TestDriver.php';

class TestServiceThree extends \Yaoi\Service {
    /**
     * @return \Yaoi\Service\Settings
     */
    protected static function getSettingsClassName()
    {
        return \Yaoi\Service\Settings::className();
    }

}
