<?php
namespace Yaoi\Http;

use Yaoi\Http\Auth\Settings;
use Yaoi\Service;

/**
 * Class Http_Auth
 */
class Auth extends Service
{
    private $salt;
    private $users = array();
    public $title;

    const AREA_NOT_SET = 1;

    /**
     * @var Settings
     */
    protected $settings;


    public function setSalt($salt)
    {
        $this->settings->salt = $salt;
        return $this;
    }

    public function addUser($login, $passwordHash)
    {
        $this->settings->users[$login] = $passwordHash;
        return $this;
    }

    public function addUsers($users)
    {
        $this->settings->users = array_merge($this->users, $users);
        return $this;
    }

    public function isProvided()
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            return false;
        } else {
            if (!array_key_exists($_SERVER['PHP_AUTH_USER'], $this->settings->users)) {
                return false;
            } elseif ($this->settings->users[$_SERVER['PHP_AUTH_USER']]
                != $this->hash($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])
            ) {
                return false;
            }
        }
        return true;
    }

    public function isProvidedDemandOnWrong()
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            return false;
        } else {
            if (!array_key_exists($_SERVER['PHP_AUTH_USER'], $this->settings->users)) {
                $this->fatal('Unknown user ' . $_SERVER['PHP_AUTH_USER'] . print_r($this->settings, 1));
            } elseif ($this->settings->users[$_SERVER['PHP_AUTH_USER']]
                != $this->hash($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])
            ) {
                $this->fatal('Bad password');
            }
        }
        return true;
    }

    public function demand($logout = false, $redirectOnLogoutUrl = null)
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="' . $this->settings->title . '"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Cancelled';
            exit;
        } else {

            if (!array_key_exists($_SERVER['PHP_AUTH_USER'], $this->settings->users)) {
                $this->fatal('Unknown user ' . $_SERVER['PHP_AUTH_USER']);
            } elseif ($this->settings->users[$_SERVER['PHP_AUTH_USER']]
                != $this->hash($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])
            ) {
                $this->fatal('Bad password');
            }

            if ($logout) {
                if ($redirectOnLogoutUrl) {
                    $message = '<html><head><meta http-equiv="refresh" content="0;' . $redirectOnLogoutUrl . '"/></head><body>Logout</body></html>';
                } else {
                    $message = 'Logout';
                }
                $this->fatal($message);
            }
        }
    }

    public function logout()
    {
        $this->demand(true);
    }


    public function hash($login, $password)
    {
        return self::makeHash($login, $password, $this->settings->salt);
    }

    public static function makeHash($login, $password, $salt)
    {
        return md5($login . $salt . $password);
    }

    private function fatal($message)
    {
        header('WWW-Authenticate: Basic realm="' . $this->settings->title . '"');
        header('HTTP/1.0 401 Unauthorized');
        die($message);
    }

    public static function getSettingsClassName()
    {
        return Settings::className();
    }


}

