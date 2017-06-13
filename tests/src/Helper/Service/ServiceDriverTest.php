<?php

namespace YaoiTests\Helper\Service;

class TestServiceThree extends \Yaoi\Service {
    /**
     * @return \Yaoi\Service\Settings
     */
    protected static function getSettingsClassName()
    {
        return \Yaoi\Service\Settings::className();
    }

}
