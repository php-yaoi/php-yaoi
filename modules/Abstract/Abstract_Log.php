<?php

class Abstract_Log extends Base_Class
{
    private $handle;
    private $hits = 0;
    private $filename;

    public static $path = './logs/';

    private static $logs = array();

    /**
     * @param $filename
     * @return self
     */
    public static function get($filename)
    {
        if (!isset(self::$logs[$filename])) {
            self::$logs[$filename] = new self($filename);
        }
        return self::$logs[$filename];
    }


    private function __construct($filename)
    {
        $this->filename = '/' == $filename[0] ? $filename : self::$path . $filename;
    }


    public function write($message)
    {
        $message = date('Y-m-d H:i:s') . "\t" . $message . "\n";

        if (++$this->hits > 5) {
            if (is_null($this->handle)) {
                $this->handle = fopen($this->filename, 'a');
            }
            fwrite($this->handle, $message);
        } else {
            file_put_contents($this->filename, $message, FILE_APPEND);
        }
    }

}