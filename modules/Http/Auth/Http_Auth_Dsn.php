<?php

class Http_Auth_Dsn extends String_Dsn {
    public $salt;
    public $users;
    public $title = 'Restricted Area';

    public function __construct($dsnUrl = null) {
        parent::__construct($dsnUrl);
        if ($this->username && $this->password) {
            if ($this->salt) {
                $this->users[$this->username] = $this->password;
            }
            else {
                $this->users[$this->username] = Http_Auth::makeHash($this->username, $this->password, '');
            }
        }
    }
}