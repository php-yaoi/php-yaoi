<?php

namespace Yaoi\Log\Driver;

use Yaoi\App;
use Yaoi\Client;
use Yaoi\Log;
use Yaoi\Log\Driver;
use Yaoi\Log\Dsn;

class File implements Driver
{
    private $handle;
    private $hits = 0;
    private $fileName;

    public function __construct(Dsn $dsn = null)
    {
        if (null === $dsn) {
            throw new Client\Exception('Log filename required in config dsn', Client\Exception::DSN_REQUIRED);
        }

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
    public function push($message, $type = Log::TYPE_MESSAGE)
    {
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