<?php

namespace Yaoi\Request;

use Yaoi\Request;

class Auth extends Request
{
    public $username;
    public $password;
    public $csrfToken;

    public static function setUpFields() {

    }
}