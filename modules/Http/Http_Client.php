<?php

/**
 * Class Http_Client
 * TODO detect response charset
 */
class Http_Client {
    public static $globalSettings = array();

    const LOG_URL = 1;
    const LOG_POST = 2;
    const LOG_CONTEXT = 4;
    const LOG_RESPONSE_HEADERS = 8;
    const LOG_RESPONSE_BODY = 16;

    const XML_HTTP_REQUEST = 'XMLHttpRequest';

    const FILENAME_LOG = 'http_client.log';

    public $logFlags = 0;
    public $logOnce;

    /**
     * @var Log
     */
    public $logName = self::FILENAME_LOG;



    public $cookies = array();
    public $requestCharset = 'UTF-8';
    public $charset = 'UTF-8';
    public $post;
    public $url;
    public $referrer;
    public $xRequestedWith;
    public $followLocation = false;
    public $skipBadRequestException = true;
    public $responseHeaders = array();
    public $parsedHeaders = array();
    public $headers = array();
    public $defaultHeaders = array(
        'User-Agent' => 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0',
        'Accept' =>	'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Encoding' => 'gzip, deflate',
        'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
        'Connection' => 'close',
    );

    public function __construct($ignoreGlobalSettings = false) {
        $this->reset();
        if (!$ignoreGlobalSettings) {
            if (isset(self::$globalSettings['proxy'])) {
                $this->setProxy(self::$globalSettings['proxy']);
            }

            if (isset(self::$globalSettings['defaultHeaders'])) {
                $this->defaultHeaders = array_merge($this->defaultHeaders, self::$globalSettings['defaultHeaders']);
            }
        }
    }

    public function reset() {
        $this->headers = $this->defaultHeaders;
        $this->post = null;
        $this->url = null;

        return $this;
    }

    public function parseResponseCookies() {
        $cookies = array();
        $this->parsedHeaders = array();
        foreach ($this->responseHeaders as $hdr) {
            if ($p = strpos($hdr, ':')) {
                $header = substr($hdr, 0, $p);
                $value = trim(substr($hdr, $p + 1));

                $tmp = explode(';', $value);
                $valueParams = array(
                    'value' => $value,
                );
                if (count($tmp) > 1) {
                    foreach ($tmp as $tm) {
                        $tm = explode('=', trim($tm), 2);
                        if (isset($tm[1])) {
                            $valueParams[$tm[0]] = $tm[1];
                        }
                        else {
                            $valueParams['baseValue'] = $tm[0];
                        }
                    }
                }

                $this->parsedHeaders [strtolower($header)]= $valueParams;
            }

            if (preg_match('/^Set-Cookie:\s*([^;]+)/', $hdr, $matches)) {
                //echo $hdr;
                parse_str($matches[1], $tmp);
                $cookies += $tmp;
            }

            //Content-Type: text/html; charset=WINDOWS-1251
        }

        if (isset($this->parsedHeaders['content-type']['charset'])) {
            $this->charset = $this->parsedHeaders['content-type']['charset'];
        }

        $this->cookies = array_merge($this->cookies, $cookies);
    }


    protected $proxy;
    public function setProxy($dsn) {
        if ($dsn instanceof String_Dsn) {
            $this->proxy = $dsn;
        }
        else {
            $this->proxy = new String_Dsn($dsn);
        }
    }


    public function fetch() {
        $logFlags = $this->logFlags;
        if ($this->logOnce) {
            $logFlags = $this->logOnce;
            $this->logOnce = null;
        }

        $driver = new Http_ClientDriver_FileGetContents();

        if ($this->proxy) {
            $driver->setProxy($this->proxy);
        }

        $headers = $this->headers;
        if ($this->cookies) {
            $headers['Cookie'] = http_build_query($this->cookies, null, '; ');
        }

        if ($this->post) {
            $driver->setMethod('POST');
            $content = http_build_query($this->post);
            $driver->setRequestContent($content);
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            $headers['Content-Length'] = strlen($content);
            unset($content);
        }

        if ($this->referrer) {
            $headers['Referer'] = $this->referrer;
        }
        if ($this->xRequestedWith) {
            $headers['X-Requested-With'] = $this->xRequestedWith;
            $this->xRequestedWith = null;
        }
        $this->referrer = $this->url;

        if (isset($headers['Content-Type'])) {
            $headers['Content-Type'] .= '; charset=' . $this->requestCharset;
        }

        $driver->setHeaders($headers);
        $driver->setUrl($this->url);



        $log = '';
        if ($logFlags) {
            $log .= ''
                . ($logFlags & self::LOG_URL ? print_r($this->url, 1) . "\n" : '')
                . ($logFlags & self::LOG_POST ? print_r($this->post, 1) : '')
                //. ($logFlags & self::LOG_CONTEXT ? print_r($context, 1) : '')
                ;
        }

        $this->responseHeaders = array();

        if ($this->mock) {
            $response = '';
            $mock = $this->mock->branch(crc32(serialize($driver->getRequest())), $this->url);
            if ($mock instanceof Mock_DataSetPlay) {
                $response = $mock->get(null, 'response');
                $this->responseHeaders = $mock->get(null, 'responseHeaders');
            }
            elseif ($mock instanceof Mock_DataSetCapture) {
                $driver->fetch();
                $response = $driver->getResponseContent();
                $this->responseHeaders = $driver->getResponseHeaders();

                if (!$this->skipBadRequestException && false === $response) {
                    $e = new Http_ClientException('Bad request', Http_ClientException::BAD_REQUEST);
                    $e->request = $driver->getRequest();
                    $e->responseHeaders = $this->responseHeaders;
                    $e->url = $this->url;
                    throw $e;
                }

                $mock->add(null, $response, 'response');
                $mock->add(null, $this->responseHeaders, 'responseHeaders');
            }
        }
        else {
            $driver->fetch();
            $response = $driver->getResponseContent();
            $this->responseHeaders = $driver->getResponseHeaders();

            if (!$this->skipBadRequestException && false === $response) {
                $e = new Http_ClientException('Bad request', Http_ClientException::BAD_REQUEST);
                $e->request = $driver->getRequest();
                $e->responseHeaders = $this->responseHeaders;
                $e->url = $this->url;
                throw $e;
            }
        }


        if ($logFlags) {
            $log .= ''
                . ($logFlags & self::LOG_RESPONSE_HEADERS ? print_r($this->responseHeaders, 1) : '')
                . ($logFlags & self::LOG_RESPONSE_BODY ? print_r($response, 1) : '')
                ;
            Log::get($this->logName)->write($log);
        }

        //print_r($this->responseHeaders);
        $this->parseResponseCookies();
        //print_r($response);

        if ($this->followLocation) {
            if (!empty($this->parsedHeaders['location'])) {
                $this->post = null;
                $this->url = $this->parsedHeaders['location']['value'];
                return $this->fetch();
            }
        }


        if (!empty($this->parsedHeaders['content-encoding'])) {

            if ('gzip' == strtolower($this->parsedHeaders['content-encoding']['value'])) {
                if (!function_exists('gzdecode')) {
                    $response = gzinflate(substr($response, 10, -8));
                }
                else {
                    $response = gzdecode($response);
                }
            }

            elseif ('deflate' == strtolower($this->parsedHeaders['content-encoding']['value'])) {
                $response = gzinflate($response);
            }
        }

        return $response;
    }


    /**
     * @var Mock_DataSetBase
     */
    private $mock;
    public function mock(Mock_DataSet $dataSet = null) {
        $this->mock = $dataSet;
    }


}
