<?php
/*
 * This file is a part of small-static-http
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallStaticHttp\Kernel\Configuration;

use SmallStaticHttp\File\FileLoaderInterface;
use SmallStaticHttp\File\FilesLoader;
use SmallStaticHttp\Server\Server;

class Configuration
{
    const CONFIG_FILE = '/etc/small-static-http.json';

    const SERVICES_DEFINITION = [
        FileLoaderInterface::class => [
            'alias' => 'fileLoader',
            'class' => FilesLoader::class,
            'params' => ['$root-path'],
        ],
        Server::class => [
            'alias' => 'httpServer',
            'params' => ['@' . FileLoaderInterface::class, '$http'],
        ]
    ];

}