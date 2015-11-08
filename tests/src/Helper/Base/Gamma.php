<?php

namespace YaoiTests\Helper\Base;


class Gamma extends Alpha
{
    public function __construct()
    {
        $this->publicPropertyGamma = 'g0';
        $this->protectedPropertyGamma = 'g1';
        $this->privatePropertyGamma = 'g2';
    }

    public $publicPropertyGamma;
    protected $protectedPropertyGamma;
    private $privatePropertyGamma;
}