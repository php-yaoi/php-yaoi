<?php
use Yaoi\Http\Client\UploadFile;
use Yaoi\Http\Client;
use Yaoi\Mock;
use Yaoi\Mock\DataSetCapture;
use Yaoi\Mock\DataSetPlay;
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
        $storage = new Storage('serialized-file://localhost/tests/resources/mocked-data-sets/HttpUploadTest.dat?compression=1');

        $mockSet = new Mock($storage);
        //$storage->deleteAll(); $mockSet = new Mock_DataSetCapture($storage);

        \Yaoi\Date\Source::getInstance()->mock($mockSet);

        $client = Client::create();
        $mirror = TestCase::$settings['envHttpMirrorServer'];
        $client->url = 'http://' . $mirror . '/test/ooo.html/?dasd=34';

        $client->post = array(
            'first' => 'simple post',
            'uploaded_file' => UploadFile::createByContent('test file contents'),
            'upload2' => UploadFile::createByPath('mocked-data-sets/dummy'),
            'foo' => 'bar',
        );


        $res = $client->fetch();
        if ($mockSet instanceof DataSetPlay) {
            $this->assertSame(str_replace($mockSet->get('mirror'), $mirror, $mockSet->get('result')), $res);
        }
        elseif ($mockSet instanceof DataSetCapture) {
            $mockSet->add($mirror, 'mirror');
            $mockSet->add($res, 'result');
        }

        //echo $res;
    }
} 