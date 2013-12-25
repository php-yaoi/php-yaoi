<?php

/**
 * Class Http_Auth
 * @method static Http_Auth create($salt, $users = array(), $title = 'Restricted Area')
 */
class Http_Auth extends Base_Class {
    private $salt;
    private $users = array();
    public $title;

    const AREA_NOT_SET = 1;
    public static $areas = array();

    public function __construct($salt, $users = array(), $title = 'Restricted Area') {
        $this->salt = $salt;
        $this->users = $users;
        $this->title = $title;
    }

    public function addUser($login, $passwordHash) {
        $this->users[$login] = $passwordHash;
        return $this;
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
                != $this->hash($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
                $this->fatal('Bad password');
            }

            if ($logout) {
                $this->fatal('Logout');
            }
        }
    }

    public function logout() {
        $this->check(true);
    }


    public function hash($login, $password) {
        return md5($login . $this->salt . $password);
    }

    private function fatal($message) {
        header('WWW-Authenticate: Basic realm="' . $this->title . '"');
        header('HTTP/1.0 401 Unauthorized');
        die($message);
    }

}