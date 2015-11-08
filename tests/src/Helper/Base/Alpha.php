<?php

namespace YaoiTests\Helper\Base;


use Yaoi\BaseClass;

class Alpha extends BaseClass
{
    public $publicPropertyAlpha;
    protected $protectedPropertyAlpha;
    private $privatePropertyAlpha;

    public function __construct()
    {
        $this->publicPropertyAlpha = 'a0';
        $this->protectedPropertyAlpha = 'a1';
        $this->privatePropertyAlpha = 'a2';
    }

    public function getProtectedPropertyAlpha()
    {
        return $this->protectedPropertyAlpha;
    }
}