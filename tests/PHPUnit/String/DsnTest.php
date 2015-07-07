<?php
use Yaoi\String\Dsn;
use Yaoi\String\Exception;
use Yaoi\Test\PHPUnit\TestCase;



class DsnTest extends TestCase  {

    /**
     * URL-like dsn scheme
     * @see \Yaoi\String\Dsn
     */
    public function testUrlScheme() {
        $this->assertStringEqualsSpaceless(
            print_r(new Dsn('scheme://user:pass@host:1234/path/deeper/?param1=1&param2=2'), 1),
            <<<EOD
Yaoi\String\Dsn Object
(
    [scheme] => scheme
    [username] => user
    [password] => pass
    [hostname] => host
    [port] => 1234
    [path] => path/deeper/
    [param1] => 1
    [param2] => 2
)

EOD
        );
    }


    /**
     * User credential scheme (username and password separated by colon)
     * @see \Yaoi\String\Dsn
     */
    public function testCredentialsScheme() {
        $this->assertStringEqualsSpaceless(
            print_r(new Dsn('user:password'), 1),
            <<<EOD
Yaoi\String\Dsn Object
(
    [scheme] =>
    [username] => user
    [password] => password
    [hostname] =>
    [port] =>
    [path] =>
)

EOD
        );
    }


    /**
     * Scheme-only (string without "://")
     * @see \Yaoi\String\Dsn
     */
    public function testSchemeOnly() {
        $this->assertStringEqualsSpaceless(
            print_r(new Dsn('scheme'), 1),
            <<<EOD
Yaoi\String\Dsn Object
(
    [scheme] => scheme
    [username] =>
    [password] =>
    [hostname] =>
    [port] =>
    [path] =>
)

EOD
        );
    }

    /**
     * Special characters (@, :, /, ?, &) in values should be prepended with \
     * It is not required to escape space symbol
     * @see \Yaoi\String\Dsn
     */
    public function testEscape() {
        /**
         * URL scheme
         */
        $this->assertStringEqualsSpaceless(
            print_r(new Dsn('scheme://john.doe\@mail.com:p\@ssw\:rd@host?company=Smith \& Wesson'), 1),
            <<<EOD
Yaoi\String\Dsn Object
(
    [scheme] => scheme
    [username] => john.doe@mail.com
    [password] => p@ssw:rd
    [hostname] => host
    [port] =>
    [path] =>
    [company] => Smith & Wesson
)

EOD
        );


        /**
         * Credentials scheme
         */
        $this->assertStringEqualsSpaceless(
            print_r(new Dsn('john.doe\@mail.com:p\@ssw\:rd'), 1),
            <<<EOD
Yaoi\String\Dsn Object
(
    [scheme] =>
    [username] => john.doe@mail.com
    [password] => p@ssw:rd
    [hostname] =>
    [port] =>
    [path] =>
)

EOD
        );

        /**
         * Scheme-only
         */
        $this->assertStringEqualsSpaceless(
            print_r(new Dsn('sch\:eme'), 1),
            <<<EOD
Yaoi\String\Dsn Object
(
    [scheme] => sch:eme
    [username] =>
    [password] =>
    [hostname] =>
    [port] =>
    [path] =>
)

EOD
        );

    }


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

    public function testShortScheme() {
        $dsn = new Dsn('test://');
        $this->assertSame('test', $dsn->scheme);
        $this->assertSame(null, $dsn->hostname);
    }

    public function testNull() {
        $this->assertSame(true, Dsn::create() instanceof Dsn);
    }
} 