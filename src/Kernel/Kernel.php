<?php
/*
 * This file is a part of small-static-http
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallStaticHttp\Kernel;

use SmallStaticHttp\Kernel\Configuration\Configuration;
use SmallStaticHttp\Kernel\DependencyInjection\Container;
use SmallStaticHttp\File\FileLoaderInterface;

class Kernel
{

    const NAMESPACES = [
        '\SmallStaticHttp' => __DIR__ . '/..',
    ];

    public static Container $container;

    public function start(): void
    {
        // Register kernel autoloader
        include_once __DIR__ . '/Autoloader.php';
        $autoloader = new \Autoloader();
        foreach (static::NAMESPACES as $namespace => $directory) {
            $autoloader->addNamespace($namespace, $directory);
        }
        $autoloader->register();

        // Register composer autoloader
        include_once __DIR__ . '/../vendor/autoload.php';

        Log::info("Starting Kernel");

        // Create container
        self::$container = new Container();

        // Get parameters
        Log::info("Load parameters file");
        if (!is_file(Configuration::CONFIG_FILE)) {
            throw new \Exception('Configuration file ' . Configuration::CONFIG_FILE . ' not found !');
        }
        if (self::$container->loadParametersFromJson(file_get_contents(Configuration::CONFIG_FILE)) === false) {
            Log::critical("Failed to load parameters !");
            exit;
        }
    }

}