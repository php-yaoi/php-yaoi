<?php

class Http_ClientDriver_Fsockopen implements Http_ClientDriver_Interface {
    public function setUrl($url)
    {
        // TODO: Implement setUrl() method.
    }

    public function setMethod($method)
    {
        // TODO: Implement setMethod() method.
    }

    public function setRequestContent($content)
    {
        // TODO: Implement setRequestContent() method.
    }

    public function setHeaders($headers)
    {
        // TODO: Implement setHeaders() method.
    }

    public function getResponseContent()
    {
        // TODO: Implement getResponseContent() method.
    }

    public function getResponseHeaders()
    {
        // TODO: Implement getResponseHeaders() method.
    }

    public function getRequest()
    {
        // TODO: Implement getRequest() method.
    }

    public function fetch() {
        // TODO
        $fp = fsockopen($host, $port, $errno, $errstr, 10);
        if (!$fp) {
            echo "$errstr ($errno)<br />\n";
        } else {
            $out = "GET $url HTTP/1.1\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Connection: Close\r\n\r\n";
            fwrite($fp, $out);
            while (!feof($fp)) {
                $response = fgets($fp, 1024);
                print(substr($response,9,3));
            }
            fclose($fp);
        }
    }
} 