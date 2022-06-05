<?php
/*
 * This file is a part of small-static-http
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace SmallStaticHttp\File;

use SmallStaticHttp\Kernel\Kernel;

class FilesLoader implements FileLoaderInterface
{

    /** @var File[] list of files identified by uri */
    private array $files = [];
    public string | null $publicPath = null;

    /**
     * Set public path
     * @param $path
     * @return $this
     */
    public function setPublicPath($path): self
    {
        if (!is_dir($path)) {
            throw new \Exception('Invalid public path !');
        }

        $this->publicPath = $path;

        return $this;
    }

    /**
     * Load files from filesystem
     * @return $this
     * @throws \Exception
     */
    public function load(): self
    {
        $this->findAll($this->publicPath);

        return $this;
    }

    /**
     * List files by uri
     * @param string $uri
     * @return File
     * @throws FileNotFoundException
     */
    public function getFileByUri(string $uri): File
    {
        if (!array_key_exists($uri, $this->files)) {
            throw new FileNotFoundException('File not found');
        }

        return $this->files[$uri];
    }

    /**
     * Find all files and make list of files by uri
     * @param string $path
     * @param $baseUri
     * @return void
     * @throws \Exception
     */
    private function findAll(string $path, string $baseUri = ''): void
    {
        if (!is_dir($path)) {
            throw new \Exception("$path is not a directory !");
        }

        $files = scandir($path);
        foreach ($files as $file) {
            if (Kernel::$container->getParameter('skip-hidden-files') && str_starts_with($file, '.')) {
                continue;
            }

            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $filepath = $path . '/' . $file;
            $uri = $baseUri . '/' . $file;

            if (is_dir($filepath)) {
                $this->findAll($filepath, $uri);
                continue;
            }

            if (is_file($filepath)) {
                $this->files[$uri] = new File($filepath);
            }
        }
    }

}