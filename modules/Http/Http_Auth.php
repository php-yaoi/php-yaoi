<?php

/**
 * Class Http_Auth
 * @method static Http_Auth create($salt, $title = 'Restricted Area')
 */
class Http_Auth extends Base_Class {
    private $salt;
    private $users = array();
    public $title;

    public function __construct($salt, $title = 'Restricted Area') {
        $this->salt = $salt;
        $this->title = $title;
    }

    public function addUsers($users) {
        $this->users = array_merge($this->users, $users);
        return $this;
    }

    public function check($logout = false) {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="' . $this->title . '"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Cancelled';
            exit;
        } else {

            if (!array_key_exists($_SERVER['PHP_AUTH_USER'], $this->users)) {
                $this->fatal('Unknown user');
            } elseif ($this->users[$_SERVER['PHP_AUTH_USER']]
                != md5($_SERVER['PHP_AUTH_USER'] . $this->salt . $_SERVER['PHP_AUTH_PW'])) {
                $this->fatal('Bad password');
            }

            if ($logout) {
                $this->fatal('Logout');
            }
        }
    }

    private function fatal($message) {
        header('WWW-Authenticate: Basic realm="' . $this->title . '"');
        header('HTTP/1.0 401 Unauthorized');
        die($message);
    }

}