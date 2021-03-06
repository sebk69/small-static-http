<?php
/*
 * This file is a part of small-static-http
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallStaticHttp\Server;



use SmallStaticHttp\File\FileLoaderInterface;
use SmallStaticHttp\File\FileNotFoundException;
use SmallStaticHttp\File\Mime;
use SmallStaticHttp\Kernel\Kernel;
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
            $gzip = false;
            if (isset($request->header["accept-encoding"]) && strstr($request->header["accept-encoding"], 'gzip')) {
                $gzip = true;
            }

            $index = 0;
            $indexNames = Kernel::$container->getParameter('index-files');
            $indexFile = "";
            $found = false;
            while(!$found && $index <= count($indexNames)) {
                try {
                    $file = $this->fileLoader->getFileByUri($request->server['request_uri'] . $indexFile);

                    if ($gzip) {
                        $response->header('content-encoding', 'gzip');
                    }

                    $response->header('content-type', $file->getMime());

                    $response->write($gzip ? $file->getContentGzipped() : $file->getContent());
                    $response->end();
                    Log::info('Serving ' . $request->server['request_uri']);
                    $found = true;
                } catch (FileNotFoundException $e) {
                    $indexFile = @$indexNames[$index];
                }
                $index++;
            }

            if (!$found) {
                Log::error('Can\'t serve ' . $request->server['request_uri'] . ' : file not found');

                try {
                    $file = $this->fileLoader->getFileByUri(Kernel::$container->getParameter('not-found.page'));

                    if ($gzip) {
                        $response->header('content-encoding', 'gzip');
                    }

                    $response->header('content-type', $file->getMime());

                    $response->write($gzip ? $file->getContentGzipped() : $file->getContent());
                    $response->status(Kernel::$container->getParameter('not-found.status'));
                    $response->end();

                    Log::info('Serving ' . $request->server['request_uri']);
                } catch (FileNotFoundException $e) {
                    $response->status(404);
                    $response->end('Not found !');
                }
            }
        });
    }

    public function serve()
    {
        Log::info('Starting server');
        $this->swooleServer->start();
    }

}