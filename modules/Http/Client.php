<?php
namespace Yaoi\Http;

use Yaoi\Http\Client\Exception;
use Yaoi\Http\Client\Dsn;
use Yaoi\Http\Client\Driver;
use Yaoi\Http\Client\UploadFile;
use Yaoi\Log;
use \Mock;
use \Mock_Able;
use \String_Dsn;
use \Mock_Exception;
use \Yaoi;

/**
 * Class Http_Client
 * TODO detect response charset
 * @method Driver getDriver
 */
class Client extends \Yaoi\Client implements Mock_Able
{
    protected static $dsnClass = 'Http_Client_Dsn';
    public static $conf = array();
    protected static $instances = array();
    public static $globalSettings = array();

    const XML_HTTP_REQUEST = 'XMLHttpRequest';

    public $cookies = array();
    public $requestCharset = 'UTF-8';
    public $charset = 'UTF-8';
    public $post;
    public $url;
    public $referrer;
    public $xRequestedWith;
    public $followLocation = true;
    public $skipBadRequestException = true;
    public $responseHeaders = array();
    public $parsedHeaders = array();
    public $headers = array();
    public $defaultHeaders = array(
        'User-Agent' => 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Encoding' => 'gzip, deflate',
        'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
        'Connection' => 'close',
    );

    /**
     * @var Dsn
     */
    protected $dsn;

    public function __construct($dsn = null)
    {
        if ($dsn === null) {
            $dsn = new Dsn();
        }

        parent::__construct($dsn);
        $this->reset();
        if ($this->dsn) {
            if ($this->dsn->proxy) {
                $this->setProxy($this->dsn->proxy);
            }

            if ($this->dsn->defaultHeaders) {
                $this->defaultHeaders = array_merge($this->defaultHeaders, $this->dsn->defaultHeaders);
            }

            if ($this->dsn->log) {
                $log = Log::getInstance($this->dsn->log);
                $this->logUrl($log);
                $this->logContext($log);
                $this->logResponseHeaders($log);
                $this->logResponseBody($log);
                $this->logError($log);
            }
        }

        $this->mock = Mock::getNull();
    }

    public function reset()
    {
        $this->headers = $this->defaultHeaders;
        $this->post = null;
        $this->url = null;

        return $this;
    }

    protected function parseResponseCookies()
    {
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
                        } else {
                            $valueParams['baseValue'] = $tm[0];
                        }
                    }
                }

                $this->parsedHeaders [strtolower($header)] = $valueParams;
            }

            if (preg_match('/^Set-Cookie:\s*([^;]+)/', $hdr, $matches)) {
                //echo $hdr;
                parse_str($matches[1], $tmp);
                $cookies = array_merge($cookies, $tmp);
            }

            //Content-Type: text/html; charset=WINDOWS-1251
        }

        if (isset($this->parsedHeaders['content-type']['charset'])) {
            $this->charset = $this->parsedHeaders['content-type']['charset'];
        }

        $this->cookies = array_merge($this->cookies, $cookies);
    }


    protected $proxy;

    public function setProxy($dsn)
    {
        if ($dsn instanceof String_Dsn) {
            $this->proxy = $dsn;
        } else {
            $this->proxy = new String_Dsn($dsn);
        }
    }


    private $redirectsCount = 0;

    public function fetch($url = null)
    {
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


        $uploadingFiles = false;
        if ($this->post) {
            foreach ($this->post as $item) {
                if ($item instanceof UploadFile) {
                    $uploadingFiles = true;
                    break;
                }
            }
        }


        if ($uploadingFiles) {
            $driver->setMethod('POST');

            $multipartBoundary = '--------------------------' . Yaoi::time()->microNow();
            $content = '';

            foreach ($this->post as $name => $value) {
                if ($value instanceof UploadFile) {
                    $content .= "--" . $multipartBoundary . "\r\n"
                        . "Content-Disposition: form-data; name=\"" . $name . "\"; filename=\"" . $value->getFileName() . "\"\r\n"
                        . "Content-Type: " . $value->mimeType . "\r\n\r\n"
                        . $value->getContents() . "\r\n";
                } else {
                    $content .= "--" . $multipartBoundary . "\r\n"
                        . "Content-Disposition: form-data; name=\"$name\"\r\n\r\n"
                        . "$value\r\n";
                }
            }
            $content .= "--" . $multipartBoundary . "--\r\n";

            $driver->setRequestContent($content);
            $headers['Content-Type'] = 'multipart/form-data; boundary=' . $multipartBoundary;
            //$headers['Content-Length'] = strlen($content);
            unset($content);
        } elseif ($this->post) {
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

        if (isset($headers['Content-Type']) && !$uploadingFiles) {
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

        $mock = $this->mock->branch($this->url, hash('crc32b', serialize($driver->getRequest())));
        if ($this->logContext) {
            $this->logContext->push($driver->getRequest());
        }

        $self = $this;
        try {
            list($response, $this->responseHeaders) = $mock->branch('responseData')
                ->get(null, function () use ($driver, $self, $mock) {
                    $driver->fetch();
                    $response = $driver->getResponseContent();
                    $responseHeaders = $driver->getResponseHeaders();

                    if (!$self->skipBadRequestException && false === $response) {
                        $e = new Exception('Bad request', Exception::BAD_REQUEST);
                        $e->request = $driver->getRequest();
                        $e->responseHeaders = $self->responseHeaders;
                        $e->url = $self->url;
                        throw $e;
                    }

                    return array($response, $responseHeaders);
                });
        } catch (Mock_Exception $e) {
            if ($this->logError) {
                $this->logError->push($e->getMessage()
                    . ', request: ' . print_r($driver->getRequest(), 1)
                    . ', serialize: ' . base64_encode(serialize($driver->getRequest())));
            }
            throw $e;
        }


        if ($this->logResponseHeaders) {
            $this->logResponseHeaders->push(print_r($this->responseHeaders, 1));
        }

        if ($this->logRawResponseBody) {
            $this->logRawResponseBody->push($response);
        }


        $this->parseResponseCookies();

        if ($this->followLocation) {
            if (!empty($this->parsedHeaders['location'])) {
                $this->post = null;
                $redirectUrl = $this->parsedHeaders['location']['value'];
                $this->url = $this->getAbsoluteUrl($redirectUrl);
                if (++$this->redirectsCount > 5) {
                    return false;
                }
                return $this->fetch();
            }
        }


        if (!empty($this->parsedHeaders['content-encoding'])) {

            if ($response && 'gzip' == strtolower($this->parsedHeaders['content-encoding']['value'])) {
                if (!function_exists('gzdecode')) {
                    $response = gzinflate(substr($response, 10, -8));
                } else {
                    $response = gzdecode($response);
                }
            } elseif ('deflate' == strtolower($this->parsedHeaders['content-encoding']['value'])) {
                $response = gzinflate($response);
            }
        }

        if ($this->logResponseBody) {
            $this->logResponseBody->push($response);
        }

        return $response;
    }


    public function getAbsoluteUrl($url)
    {
        $parsed = parse_url($this->url);

        // TODO //host/path, /path, scheme://host/path, path, ?query

        // "//host/..."
        if ('//' == substr($url, 0, 2)) {
            $url = $parsed['scheme'] . ':' . $url;
        } // "/path..."
        elseif ('/' == $url[0]) {
            $base = substr($this->url, 0, strpos($this->url, '/', 8));
            $url = $base . $url;
        } // "http(s)://...."
        elseif (strpos($url, '://')) {

        } // "?query..."
        elseif ('?' == $url[0]) {
            $pos = strpos($this->url, '?');
            $base = $pos ? substr($this->url, 0, $pos) : $this->url;
            $url = $base . $url;
        } // "path..."
        else {
            $pos = strrpos($this->url, '/');
            $base = $pos ? substr($this->url, 0, $pos + 1) : $this->url;
            $url = $base . $url;
        }

        return $url;
    }


    /**
     * @var Mock
     */
    private $mock;

    public function mock(Mock $dataSet = null)
    {
        if (null === $dataSet) {
            $dataSet = Mock::getNull();
        }
        $this->mock = $dataSet;
    }


    /**
     * @var Log
     */
    private $logError;

    public function logError(Log $log = null)
    {
        $this->logError = $log;
    }


    /**
     * @var Log
     */
    private $logUrl;

    public function logUrl(Log $log = null)
    {
        $this->logUrl = $log;
        return $this;
    }


    /**
     * @var Log
     */
    private $logPost;

    public function logPost(Log $log = null)
    {
        $this->logPost = $log;
        return $this;
    }

    /**
     * @var Log
     */
    private $logContext;

    public function logContext(Log $log = null)
    {
        $this->logContext = $log;
        return $this;
    }

    /**
     * @var Log
     */
    private $logResponseHeaders;

    public function logResponseHeaders(Log $log = null)
    {
        $this->logResponseHeaders = $log;
        return $this;
    }


    /**
     * @var Log
     */
    private $logRawResponseBody;

    public function logRawResponseBody(Log $log = null)
    {
        $this->logRawResponseBody = $log;
        return $this;
    }

    /**
     * @var Log
     */
    private $logResponseBody;

    public function logResponseBody(Log $log = null)
    {
        $this->logResponseBody = $log;
        return $this;
    }


}
