<?php

namespace Yaoi;


use Yaoi\BaseClass;

/**
 * Class Controller
 * @package Yaoi
 * @method static $this create(Request $request)
 */
class Controller extends BaseClass
{
    protected $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }
}