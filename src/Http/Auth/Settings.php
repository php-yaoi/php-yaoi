<?php

namespace Yaoi\Http\Auth;

use Yaoi\Http\Auth;

class Settings extends \Yaoi\Service\Settings
{
    public $salt;
    public $users;
    public $title = 'Restricted Area';

    public function __construct($dsnUrl = null)
    {
        parent::__construct($dsnUrl);
        if ($this->username && $this->password) {
            if ($this->salt) {
                $this->users[$this->username] = $this->password;
            } else {
                $this->users[$this->username] = Auth::makeHash($this->username, $this->password, '');
            }
        }
    }
}