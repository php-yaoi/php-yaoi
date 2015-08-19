<?php

namespace Yaoi\Migration;

use Yaoi\Storage;

class Settings extends \Yaoi\Service\Settings
{
    /**
     * @var \Yaoi\Storage|\Yaoi\Storage\Settings|string
     */
    public $storage;
}