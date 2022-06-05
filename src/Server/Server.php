<?php
/*
 * This file is a part of small-static-http
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallStaticHttp\Server;



use SmallStaticHttp\File\FileLoaderInterface;
use SmallStaticHttp\Kernel\Log;

class Server
{

    private \Swoole\Http\Server $swooleServer;

    public function __construct(protected FileLoaderInterface $fileLoader, array $config)
    {
        // Define server
        $this->swooleServer = new \Swoole\Http\Server('0.0.0.0', $config['port']);
        $this->swooleServer->set($config['swoole']);

        // Handle request
        $this->swooleServer->on('Request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            try {
                $file = $this->fileLoader->getFileByUri($request->server['request_uri']);

                $response->end($file->getContent());
                Log::info('Serving ' . $request->server['request_uri']);
            } catch (\Exception $e) {
                Log::info('Can\'t serve ' . $request->server['request_uri'] . ' : file not found');
                $response->status = 404;
                $response->end('Not found !');
            }
        });
    }

    public function serve()
    {
        Log::info('Starting server');
        $this->swooleServer->start();
    }

}