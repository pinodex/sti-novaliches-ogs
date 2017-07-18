<?php

/*
 * This file is part of the SIS for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\Import;

use Cache;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Collection;

abstract class Importer
{
    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var int|\DateTime
     */
    protected $duration;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var array
     */
    protected $contents;

    /**
     * @var array
     */
    protected $defaults;

    /**
     * @var bool
     */
    protected $isLocked = false;

    /**
     * Constructs Importer
     * 
     * @param string $sessionId Cache entry identifier
     * @param string $filePath Path to file
     * @param array $defaults Default values of blank column values
     * @param int|\DateTime $expiration Session duration in minutes or in DateTime object
     */
    public function __construct($sessionId, $filePath, $defaults = [], $duration = 60)
    {
        $this->sessionId = $sessionId;
        $this->filePath = $filePath;
        $this->defaults = $defaults;
        $this->duration = $duration;

        $this->contents = new Collection;
    }

    /**
     * Create new instance of Importer
     * 
     * @param string $filePath Path to file
     * @param array $defaults Default values of blank column values
     * @param int|\DateTime $duration Session duration in minutes or in DateTime object
     * 
     * @return \App\Components\Import\Importer
     */
    public static function create($filePath, $defaults = [], $duration = 60)
    {
        $uuid = Uuid::uuid4()->toString();
        
        return (new static($uuid, $filePath, $defaults, $duration))
            ->loadContents()
            ->write();
    }

    /**
     * Get an instance of importer by session ID
     * 
     * @return \App\Components\Import\Importer
     */
    public static function get($sessionId)
    {
        $cacheKey = sprintf('import:%s', $sessionId);

        return Cache::get($cacheKey);
    }

    /**
     * Get cache key
     * 
     * @return string
     */
    public function getCacheKey()
    {
        return sprintf('import:%s', $this->getSessionId());
    }

    /**
     * Get session ID
     * 
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Get finish status
     * 
     * @return bool
     */
    public function isLocked()
    {
        return $this->isLocked;
    }

    /**
     * Read data to memory
     * 
     * @return \App\Components\Import\Importer
     */
    public function read()
    {
        return Cache::get($this->getCacheKey());
    }

    /**
     * Write data to memory
     * 
     * @return \App\Components\Import\Importer
     */
    public function write()
    {
        Cache::put($this->getCacheKey(), $this, $this->duration);

        return $this;
    }

    /**
     * Delete importer session
     */
    public function delete()
    {
        Cache::forget($this->getCacheKey());
    }

    /**
     * Get sheet contents
     * 
     * @return mixed
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Set the lock flag to true
     */
    public function lock()
    {
        $this->isLocked = true;

        $this->write();
    }

    /**
     * Get sheet contents
     * 
     * @return array
     */
    abstract public function loadContents();

    /**
     * Import contents to database
     */
    abstract public function import();
}
