<?php

/**
 * Class Http_Client
 * TODO detect response charset
 * @method Http_Client_Driver getDriver
 */
class Http_Client extends Client implements Mock_Able {
    public static $conf = array();
    public static $globalSettings = array();

    const XML_HTTP_REQUEST = 'XMLHttpRequest';

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

    public function __construct(Http_Client_Dsn $dsn = null) {
        parent::__construct($dsn);
        if (null === $dsn) {
            $dsn = $this->dsn = new Http_Client_Dsn();
        }
        $this->reset();
        if ($dsn) {
            if ($dsn->proxy) {
                $this->setProxy($dsn->proxy);
            }

            if ($dsn->defaultHeaders) {
                $this->defaultHeaders = array_merge($this->defaultHeaders, $dsn->defaultHeaders);
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


    public function fetch($url = null) {
        if (null !== $url) {
            $this->url = $url;
        }

        $driver = $this->getDriver();
        $driver->reset();

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

        if ($this->logUrl) {
            $this->logUrl->push($this->url);
        }

        if ($this->logPost) {
            $this->logPost->push(print_r($this->post, 1));
        }

        $this->responseHeaders = array();

        if ($this->mock) {
            $response = '';
            $mock = $this->mock->branch($this->url, hash('crc32b', serialize($driver->getRequest())));
            if ($mock instanceof Mock_DataSetPlay) {
                try {
                    $response = $mock->branch('response')->get();
                    $this->responseHeaders = $mock->branch('responseHeaders')->get();
                }
                catch (Mock_Exception $e) {
                    if ($this->logError) {
                        $this->logError->push($e->getMessage()
                            . ', request: ' . print_r($driver->getRequest(), 1)
                            . ', serialize: ' . base64_encode(serialize($driver->getRequest())));
                    }
                    throw $e;
                }
            }
            elseif ($mock instanceof Mock_DataSetCapture) {
                $driver->fetch();
                $response = $driver->getResponseContent();
                $this->responseHeaders = $driver->getResponseHeaders();

                if (!$this->skipBadRequestException && false === $response) {
                    $e = new Http_Client_Exception('Bad request', Http_Client_Exception::BAD_REQUEST);
                    $e->request = $driver->getRequest();
                    $e->responseHeaders = $this->responseHeaders;
                    $e->url = $this->url;
                    throw $e;
                }

                $mock->branch('response')->add($response);
                $mock->branch('responseHeaders')->add($this->responseHeaders);
            }
        }
        else {
            $driver->fetch();
            $response = $driver->getResponseContent();
            $this->responseHeaders = $driver->getResponseHeaders();

            if (!$this->skipBadRequestException && false === $response) {
                $e = new Http_Client_Exception('Bad request', Http_Client_Exception::BAD_REQUEST);
                $e->request = $driver->getRequest();
                $e->responseHeaders = $this->responseHeaders;
                $e->url = $this->url;
                throw $e;
            }
        }


        if ($this->logResponseHeaders) {
            $this->logResponseHeaders->push(print_r($this->responseHeaders, 1));
        }

        if ($this->logResponseBody) {
            $this->logResponseBody->push(print_r($response, 1));
        }


        $this->parseResponseCookies();

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


    /**
     * @var Log
     */
    private $logError;
    public function logError(Log $log = null) {
        $this->logError = $log;
    }


    /**
     * @var Log
     */
    private $logUrl;
    public function logUrl(Log $log = null) {
        $this->logUrl = $log;
        return $this;
    }


    /**
     * @var Log
     */
    private $logPost;
    public function logPost(Log $log = null) {
        $this->logPost = $log;
        return $this;
    }

    /**
     * @var Log
     */
    private $logContext;
    public function logContext(Log $log = null) {
        $this->logContext = $log;
        return $this;
    }

    /**
     * @var Log
     */
    private $logResponseHeaders;
    public function logResponseHeaders(Log $log = null) {
        $this->logResponseHeaders = $log;
        return $this;
    }


    /**
     * @var Log
     */
    private $logResponseBody;
    public function logResponseBody(Log $log = null) {
        $this->logResponseBody = $log;
        return $this;
    }

}
