<?php
namespace PHPUnit\Http\Client;

use Yaoi\Http\Client;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Http\Client\MirrorTestCase;
use YaoiTests\Http\ClientHelper;

class FetchTest extends MirrorTestCase
{

    /**
     * You can specify relative urls as fetch argument
     *
     * @see Yaoi\Http\Client::fetch
     */
    public function testRelativeUrl() {
        $client = new Client();
        $client->fetch('http://' . TestCase::$settings['envHttpMirrorServer'] . '/');
        $client->fetch('/relative/path');
        $client->fetch('sibling-path');
        $response = $client->fetch('?query=string');
        $responseParsed = ClientHelper::parseRequest($response);

        $this->assertSame('GET /relative/sibling-path?query=string HTTP/1.', substr($responseParsed['Head'], 0, -1));
        $this->assertSame('http://' . TestCase::$settings['envHttpMirrorServer'] . '/relative/sibling-path', $responseParsed['Referer']);

    }

}