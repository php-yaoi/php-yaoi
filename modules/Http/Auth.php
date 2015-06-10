<?php
namespace Yaoi\Http;

use Yaoi\Http\Auth\Dsn;
use Yaoi\Client;

/**
 * Class Http_Auth
 */
class Auth extends Client
{
    protected static $dsnClass = 'Http_Auth_Dsn';
    public static $conf = array();
    protected static $instances = array();

    private $salt;
    private $users = array();
    public $title;

    const AREA_NOT_SET = 1;

    /**
     * @var Dsn
     */
    protected $dsn;


    public function setSalt($salt)
    {
        $this->dsn->salt = $salt;
        return $this;
    }

    public function addUser($login, $passwordHash)
    {
        $this->dsn->users[$login] = $passwordHash;
        return $this;
    }

    public function addUsers($users)
    {
        $this->dsn->users = array_merge($this->users, $users);
        return $this;
    }

    public function isProvided()
    {
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            return false;
        } else {
            if (!array_key_exists($_SERVER['PHP_AUTH_USER'], $this->dsn->users)) {
                return false;
            } elseif ($this->dsn->users[$_SERVER['PHP_AUTH_USER']]
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
            if (!array_key_exists($_SERVER['PHP_AUTH_USER'], $this->dsn->users)) {
                $this->fatal('Unknown user ' . $_SERVER['PHP_AUTH_USER'] . print_r($this->dsn, 1));
            } elseif ($this->dsn->users[$_SERVER['PHP_AUTH_USER']]
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
            header('WWW-Authenticate: Basic realm="' . $this->dsn->title . '"');
            header('HTTP/1.0 401 Unauthorized');
            echo 'Cancelled';
            exit;
        } else {

            if (!array_key_exists($_SERVER['PHP_AUTH_USER'], $this->dsn->users)) {
                $this->fatal('Unknown user ' . $_SERVER['PHP_AUTH_USER']);
            } elseif ($this->dsn->users[$_SERVER['PHP_AUTH_USER']]
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
        return self::makeHash($login, $password, $this->dsn->salt);
    }

    public static function makeHash($login, $password, $salt)
    {
        return md5($login . $salt . $password);
    }

    private function fatal($message)
    {
        header('WWW-Authenticate: Basic realm="' . $this->dsn->title . '"');
        header('HTTP/1.0 401 Unauthorized');
        die($message);
    }

}

