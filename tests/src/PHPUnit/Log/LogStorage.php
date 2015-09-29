<?php

namespace YaoiTests\PHPUnit\Log;

use App;
use Yaoi;
use Yaoi\Log;
use Yaoi\Log\Settings;
use Yaoi\Storage;
use Yaoi\Storage\PhpVar;
use Yaoi\Test\PHPUnit\TestCase;


class LogStorage extends TestCase
{
    public function testLog()
    {
        $db = Yaoi\Database::getInstance('test_mysqli')->mock();
        $storage = new PhpVar();
        $logDsn = new Settings('storage');
        $logDsn->storage = $storage;
        $db->log(new Log($logDsn));

        $db->query("SELECT NOW()");
        $db->query("SELECT UNIX_TIMESTAMP()");

        //print_r($storage);
        //var_export($storage->exportArray());
        $this->assertSame(array(
            0 => '(1) SELECT NOW()',
            1 => '(1) SELECT UNIX_TIMESTAMP()',
        ), $storage->exportArray());

        $db->log();
    }

    public function testStorage()
    {
        Storage::$instanceConfig ['debug-log'] = 'php-var';
        $db = App::database('test_mysqli')->mock()->log(new Log('storage:///?storage=debug-log'));

        $db->query("SELECT NOW()");
        $db->query("SELECT UNIX_TIMESTAMP()");


        $this->assertSame(array(
            0 => '(1) SELECT NOW()',
            1 => '(1) SELECT UNIX_TIMESTAMP()',
        ), Storage::getInstance('debug-log')->exportArray());
    }

}