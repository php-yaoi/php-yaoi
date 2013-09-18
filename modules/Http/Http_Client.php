<?php

/**
 * Class Http_Client
 * TODO detect response charset
 */
class Http_Client {
    const LOG_URL = 1;
    const LOG_POST = 2;
    const LOG_CONTEXT = 4;
    const LOG_RESPONSE_HEADERS = 8;
    const LOG_RESPONSE_BODY = 16;

    const FILENAME_LOG = 'http_client.log';

    public $logFlags = 0;
    public $logOnce;

    /**
     * @var Log
     */
    public $logName = self::FILENAME_LOG;



    public $cookies = array();
    public $post;
    public $url;
    public $referrer;
    public $followLocation = false;
    public $responseHeaders = array();
    public $headers = array();
    public $defaultHeaders = array(
        'User-Agent' => 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0',
    );

    public function reset() {
        $this->headers = $this->defaultHeaders;
        $this->post = null;
        $this->url = null;

        return $this;
    }

    public function parseResponseCookies() {
        $cookies = array();
        foreach ($this->responseHeaders as $hdr) {
            if (preg_match('/^Set-Cookie:\s*([^;]+)/', $hdr, $matches)) {
                //echo $hdr;
                parse_str($matches[1], $tmp);
                $cookies += $tmp;
            }
        }
        $this->cookies = array_merge($this->cookies, $cookies);
    }

    public function fetch() {
        $logFlags = $this->logFlags;
        if ($this->logOnce) {
            $logFlags = $this->logOnce;
            $this->logOnce = null;
        }

        $context = array(
            'http' => array(
                'method' => 'GET',
                'follow_location' => $this->followLocation, // don't or do follow redirects
                'header' => '',
            ),
        );
        $headers = $this->headers;
        if ($this->cookies) {
            $headers['Cookie'] = http_build_query($this->cookies, null, '; ');
        }

        if ($this->post) {
            $context['http']['method'] = 'POST';
            $context['http']['content'] = http_build_query($this->post);
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            $headers['Content-Length'] = strlen($context['http']['content']);
        }

        if ($this->referrer) {
            $headers['Referer'] = $this->referrer;
        }
        $this->referrer = $this->url;

        foreach ($headers as $type => $value) {
            $context['http']['header'] .= $type . ': ' . $value . "\r\n";
        }


        $log = '';
        if ($logFlags) {
            $log .= ''
                . ($logFlags & self::LOG_URL ? print_r($this->url, 1) . "\n" : '')
                . ($logFlags & self::LOG_POST ? print_r($this->post, 1) : '')
                . ($logFlags & self::LOG_CONTEXT ? print_r($context, 1) : '')
                ;

        }

        $context = stream_context_create($context);
        $response = file_get_contents($this->url, false, $context);
        foreach ($http_response_header as $hdr) {
            $this->responseHeaders []= $hdr;
        }
        if ($logFlags) {
            $log .= ''
                . ($logFlags & self::LOG_RESPONSE_HEADERS ? print_r($http_response_header, 1) : '')
                . ($logFlags & self::LOG_RESPONSE_BODY ? print_r($response, 1) : '')
                ;
            Log::get($this->logName)->write($log);
        }

        //print_r($this->responseHeaders);
        $this->parseResponseCookies();
        //print_r($response);

        return $response;
    }


}
