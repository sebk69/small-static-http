<?php
/*
 * This file is a part of small-static-http
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallStaticHttp\File;

interface FileLoaderInterface
{

    /**
     * Load files
     * @return FileLoaderInterface
     */
    public function load(): FileLoaderInterface;

    /**
     * Get file for an uri
     * @param string $uri
     * @return FileInterface
     */
    public function getFileByUri(string $uri): FileInterface;
}