<?php

class Log_File extends Log {
    private $handle;
    private $hits = 0;
    private $fileName;

    public function __construct(String_Dsn $dsn) {
        $this->fileName = $dsn->path;
        $this->fileName = '/' == $this->fileName[0]
            ? $this->fileName
            : App::instance()->logPath . $this->fileName;
    }

    /**
     * @param $message
     * @param string $type
     * @return $this
     */
    public function push($message, $type = Log::TYPE_MESSAGE) {
        $message = App::time('log')->date('Y-m-d H:i:s') . "\t" . print_r($message, 1) . "\n";

        if (++$this->hits > 5) {
            if (is_null($this->handle)) {
                $this->handle = fopen($this->fileName, 'a');
            }
            fwrite($this->handle, $message);
        } else {
            file_put_contents($this->fileName, $message, FILE_APPEND);
        }
        return $this;
    }
}