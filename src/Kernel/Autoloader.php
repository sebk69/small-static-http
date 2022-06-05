<?php
/*
 * This file is a part of small-static-http
 * Copyright 2022 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

class Autoloader
{
    // List of registered directories
    protected array $directories = [];

    /**
     * Add a namespace base directory
     * @param string $namespace
     * @param string $directory
     * @return $this
     */
    public function addNamespace(string $namespace, string $directory): self
    {
        $this->directories[$namespace] = $directory;

        return $this;
    }

    /**
     * Register autoloader
     * @return void
     */
    public function register()
    {
        spl_autoload_register(function (string $classname) {
            // Remove first backslash
            if (substr($classname, 0, 1) == '\\') {
                $classname = substr($classname, 1);
            }

            // Get parts of full classname
            $exploded = explode('\\', $classname);

            $match = [];
            $pos = 0;
            $partialNamespace = '';
            do {
                // For each parts
                $partialNamespace .= '\\' . $exploded[$pos];

                // Check if namespace registered
                if (array_key_exists($partialNamespace, $this->directories)) {
                    $match[] = ['partialNamespace' => $partialNamespace, 'directory' => $this->directories[$partialNamespace]];
                }

                $pos++;
            } while ($pos < count($exploded));

            // Sort by namespace
            array_multisort(array_column($match, 'partialNamespace'), SORT_DESC, $match);

            // Foreach corresponding entry
            foreach ($match as $entry) {
                // Get part of namespace that is not in registered namespace
                $partialNamespace = substr($classname, strlen($entry['partialNamespace']));
                $subpath = str_replace('\\', '/', $partialNamespace);

                // Build filepath
                $fullpath = $entry['directory'] . '/' . $subpath . '.php';

                // If exists
                if (is_file($fullpath)) {
                    // Include file
                    include_once $fullpath;
                    return;
                }
            }

            // File not found
        });
    }
}