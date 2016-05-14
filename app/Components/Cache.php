<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components;

use Exception;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Basic Cache class. Uses filesystem for caching
 */
class Cache
{
    /**
     * @var Filesysten File system instance
     */
    private $fs;

    /**
     * @var string Directory where cache files are stored
     */
    private $basePath;

    /**
     * @var string Cache files prefix
     */
    private $prefix;

    /**
     * Constructs Cache
     * 
     * @param string $basePath Directory where cache files are stored
     * @param string $prefix Cache files prefix
     */
    public function __construct($basePath, $prefix = '')
    {
        $this->fs = new Filesystem();

        if (!$this->fs->exists($basePath)) {
            $this->fs->mkdir($basePath);
        }

        $this->basePath = $basePath;
        $this->prefix = $prefix;

        if (!empty($this->prefix)) {
            $this->prefix .= '.';
        }
    }

    /**
     * Get full file path to cache file by key
     * 
     * @param string $key Cache key
     * 
     * @return string
     */
    public function getFilePathFromKey($key)
    {
        return rtrim($this->basePath, '/') . '/' . hash('sha1', $this->prefix . $key) . '.cache';
    }

    /**
     * Check if cache entry exists
     * 
     * @param string $key Cache key
     * 
     * @return boolean
     */
    public function has($key)
    {
        return $this->fs->exists($this->getFilePathFromKey($key));
    }

    /**
     * Get cache entry value
     * 
     * @param string $key Cache key
     * 
     * @return mixed
     */
    public function get($key)
    {
        try {
            $itemFile = $this->getFilePathFromKey($key);
            return unserialize(file_get_contents($itemFile));
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Put value to cache entry
     * 
     * @param string $key Cache key
     * @param mixed $value Cache value
     */
    public function put($key, $value)
    {
        $itemFile = $this->getFilePathFromKey($key);
        $this->fs->dumpFile($itemFile, serialize($value));
    }

    /**
     * Remove cache entry
     * 
     * @param string $key Cache key
     */
    public function remove($key)
    {
        $this->fs->remove($this->getFilePathFromKey($key));
    }
}
