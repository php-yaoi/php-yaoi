<?php

use Yaoi\Database;
use Yaoi\Log;
use Yaoi\Migration;
use Yaoi\Storage\PhpVar;
use Yaoi\Test\PHPUnit\TestCase;

//Database::register(Yaoi\Service::PRIMARY, 'mysqli://root@localhost/test1');
//Database::register('test_mysqli', \Yaoi\Service::PRIMARY);
//Database::register('test_pgsql', 'pgsql://postgres@localhost/travis_ci_test');
//Database::register('test_pdo_pgsql', 'pdo-pgsql://postgres@localhost/travis_ci_test');
Log::register(Yaoi\Service::PRIMARY, 'stdout');
error_reporting(E_ALL);
ini_set('display_errors', 1);

//TestCase::$settings['envHttpPHPServer'] = '127.0.0.1:8000';
//TestCase::$settings['envHttpMirrorServer'] = '127.0.0.1:1337';
//TestCase::$settings['envMemcache'] = extension_loaded('memcache');
//TestCase::$settings['envMongo'] = extension_loaded('mongo');
TestCase::$settings['envHttpPHPServer'] = false;
TestCase::$settings['envHttpMirrorServer'] = false;
TestCase::$settings['envMemcache'] = false;
TestCase::$settings['envMongo'] = false;

Migration\Manager::register(Yaoi\Service::PRIMARY, function(){
    $dsn = new Migration\Settings();
    $dsn->storage = new PhpVar();
    return $dsn;
});
