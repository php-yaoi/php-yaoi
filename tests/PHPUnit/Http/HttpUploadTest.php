<?php
use Yaoi\Http\Client\UploadFile;
use Yaoi\Http\Client;
use Yaoi\Mock;
use Yaoi\Storage;
use Yaoi\Test\PHPUnit\TestCase;

class HttpUploadTest extends TestCase {
    public function setUp()
    {
        if (empty(TestCase::$settings['envHttpMirrorServer'])) {
            $this->markTestSkipped('HttpMirror tests disabled');
            return;
        }
    }



    public function testClientUpload() {
        $storage = new Storage('serialized-file://localhost/tests/resources/mocked-data-sets/HttpUploadTest.serialized');

        $mockSet = new Mock($storage);
        //$storage->deleteAll(); $mockSet = new Mock_DataSetCapture($storage);

        \Yaoi\Date\TimeMachine::getInstance()->mock($mockSet);

        $client = Client::create();
        $mirror = TestCase::$settings['envHttpMirrorServer'];
        $client->url = 'http://' . $mirror . '/test/ooo.html/?dasd=34';

        $client->post = array(
            'first' => 'simple post',
            'uploaded_file' => UploadFile::createByContent('test file contents'),
            'upload2' => UploadFile::createByPath('tests/resources/mocked-data-sets/dummy'),
            'foo' => 'bar',
        );


        $res = $client->fetch();

        $request = $mockSet->get('request', function() use ($res, $mirror) {
            return array(
                'mirror' => $mirror,
                'response' => $res,
            );
        });

        $expected = str_replace($request['mirror'], $mirror, $request['response']);

        $expectedParsed = \YaoiTests\Http\ClientHelper::parseRequest($expected);
        $resParsed = \YaoiTests\Http\ClientHelper::parseRequest($res);


        $this->assertArraySubset(
            $expectedParsed,
            $resParsed,
            false,
            print_r(array_diff($resParsed, $expectedParsed), 1)
        );
    }
} 