<?php
/*
 * This file is a part of small-static-http
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallStaticHttp\File;

interface FileInterface
{
    public function getExtension(): string;
    public function getMime(): string;
    public function getFilename(): string;
    public function getContent(): string;
    public function getContentGzipped(): string;
}