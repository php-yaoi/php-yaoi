<?php

class Http_Client_UploadFile {
    const DEFAULT_MIME_TYPE = 'application/octet-stream';
    const DEFAULT_FILE_NAME = 'attachment';

    public $mimeType;

    private $path;
    private $fileName;
    private $contents;

    public static function createByPath($path, $mimeType = self::DEFAULT_MIME_TYPE) {
        $file = new self;
        $file->path = $path;
        $file->mimeType = $mimeType;
        return $file;
    }

    public static function createByContent($contents, $fileName = self::DEFAULT_FILE_NAME, $mimeType = self::DEFAULT_MIME_TYPE) {
        $file = new self;
        $file->fileName = $fileName;
        $file->contents = $contents;
        $file->mimeType = $mimeType;
        return $file;
    }

    public function getContents() {
        if (null === $this->path) {
            return $this->contents;
        }
        else {
            return file_get_contents($this->path);
        }
    }

    public function getFileName() {
        if (null === $this->path) {
            return $this->fileName;
        }
        else {
            return basename($this->path);
        }
    }

}