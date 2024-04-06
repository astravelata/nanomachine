<?php

namespace Astravelata\Nanomachine\Cache;

/**
 * Class PageCache
 * Handles caching of pages using APCu if it is available.
 */
class PageCache {

    /**
     * @var bool $enabled Indicates if the APCu cache is enabled.
     */
    private $enabled = false;
    /**
     * PageCache constructor
     * Checks if APCu is available and enabled. If yes, sets $enabled to true.
     */
    public function __construct() {
        $apcu_available = function_exists('apcu_enabled') && apcu_enabled();
        if ($apcu_available) {
            $this->enabled = true;
        }
    }

    /**
     * Check if the cache is enabled.
     * 
     * @return bool Returns true if the cache is enabled, false otherwise.
     */
    public function enabled(): bool {
        return $this->enabled;
    }

    /**
     * Disables the cache.
     */
    public function disable(): void {
        $this->enabled = false;
    }

    /**
     * Fetches the cached content for a specific request URI.
     * 
     * @param string $request_uri The request URI to fetch the cached content for.
     * @return string|null Returns the cached content if found, null otherwise.
     */
    public function get(string $request_uri): ?string {
        if (!$this->enabled) {
            return null;
        }

        $success = false;
        $result = apcu_fetch($this->getCacheKey($request_uri), $success);
        if ($success) {
            return $result;
        }
        return null;
    }

    /**
     * Stores content in the cache for a specific request URI.
     * 
     * @param string $request_uri The request URI to store the content for.
     * @param string $buffer The content to store in the cache.
     * @param int $ttl The time-to-live for the cache entry, in seconds.
     */
    public function set(string $request_uri, string $buffer, int $ttl=30): void {
        if (!$this->enabled) {
            return;
        }

        apcu_store($this->getCacheKey($request_uri), $buffer, $ttl);
        return;
    }

    /**
     * Generates a cache key for a given path.
     * 
     * @param string $path The path to generate the cache key for.
     * @return string Returns a sha1 hash of the path.
     */
    public function getCacheKey(string $path): string {
        return sha1($path);
    }

}
