<?php
/*
 * This file is a part of small-static-http
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallStaticHttp\File;

class File implements FileInterface
{

    protected string $mime;
    protected string $content;
    protected string $contentGzipped;

    public function __construct(protected string $filename) {
        $ext = $this->getExtension();
        $this->mime = array_key_exists($ext, Mime::TYPES) ? Mime::TYPES[$ext] : 'application/octet-stream';
        $this->content = file_get_contents($this->filename);
        $this->contentGzipped = gzencode($this->content, 9, FORCE_GZIP);
    }

    /**
     * Get file extension
     * @return string
     */
    public function getExtension(): string
    {
        for($i = strlen($this->filename); substr($this->filename, $i, 1) != '.' && $i > 0; $i--);

        if ($i > 0) {
            return substr($this->filename, $i + 1);
        }

        return '';
    }

    /**
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getContentGzipped(): string
    {
        return $this->contentGzipped;
    }

}