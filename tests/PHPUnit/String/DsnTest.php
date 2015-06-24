<?php
use Yaoi\String\Dsn;
use Yaoi\String\Exception;
use Yaoi\Test\PHPUnit\TestCase;



class DsnTest extends TestCase  {

    public function testDsn() {
        $dsn = new Dsn('http-dfdf://user:password@host.dsd:801/path/to/?param1=43&param2&param3=5');

        $this->assertSame($dsn->scheme, 'http-dfdf');
        $this->assertSame($dsn->hostname, 'host.dsd');
        $this->assertSame($dsn->password, 'password');
        $this->assertSame($dsn->username, 'user');
        $this->assertSame($dsn->port, 801);
        $this->assertSame($dsn->path, 'path/to/');
        $this->assertSame($dsn->param1, '43');
        $this->assertSame($dsn->param2, '');
        $this->assertSame($dsn->param3, '5');

        $dsn = new Dsn('proto://host//trailing/slash/path');
        $this->assertSame('/trailing/slash/path', $dsn->path);
    }


    public function testNoHost() {
        $this->assertEquals(new Dsn('test:///'), new Dsn('test://dummy/'));
        $this->assertSame(Dsn::create('test')->scheme, 'test');
        $this->assertSame(Dsn::create('test')->hostname, null);
    }

    /**
     * @expectedException Exception
     */
    public function testException() {
        new Dsn('12344://?ee');
    }


    public function testToString() {
        $dsn = 'http://user:password@host:34/path1/path2/?param1=23&param2=34';
        $this->assertSame($dsn, (string)Dsn::create($dsn));

        $dsn = 'http://user@host:34/?param1=23&param2=34';
        $this->assertSame($dsn, (string)Dsn::create($dsn));

    }

    public function testNull() {
        $this->assertSame(true, Dsn::create() instanceof Dsn);
    }
} 