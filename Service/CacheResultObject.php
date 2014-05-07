<?php

namespace Tedivm\StashBundle\Service;

/**
 * Simple result-object provider for the Stash class.
 *
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 */
class CacheResultObject
{
    /**
     * @var \Stash\Item
     */
    protected $cache;

    /**
     * @var null|CacheLogger
     */
    protected $logger;

    /**
     * Constructs the CacheResultObject, wraps Cache object to perform logging.
     *
     * @param \Stash\Item $cache
     * @param CacheLogger $logger
     */
    public function __construct($cache, $logger = null)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Decorates the Stash cache result object:
     * - Adds logging to the 'get' method
     * - Replaces the key for the 'getKey' method
     * - Passes through all other methods directly
     *
     * @param $name
     * @param $args
     * @return mixed|string
     */
    public function __call($name, $args)
    {
        if ($name === 'get') {
            return $this->getAndLog($args);
        } elseif ($name === 'getKey') {
            return $this->getShortenedKey();
        } else {
            return call_user_func_array(array($this->cache, $name), $args);
        }
    }

    /**
     * Removes the cache-service namespace from the start of the key for public consumption.
     *
     * @return string
     */
    protected function getShortenedKey()
    {
        $key = $this->cache->getKey();
        $parts = explode('/', $key);
        if ( (strpos($parts[0], '@@_') === 0) && (strpos(strrev($parts[0]), '@@_') === 0) ) {
            array_shift($parts);
        }

        return join('/', $parts);
    }

    /**
     * Get the value from the cache object, then logs the query.
     *
     * @param $args
     * @return mixed
     */
    protected function getAndLog($args)
    {
        $result = call_user_func_array(array($this->cache, 'get'), $args);
        $miss = $this->cache->isMiss();
        $key = $this->cache->getKey();

        if (isset($this->logger)) {
            $this->logger->logRequest($key, !($miss), $result);
        }

        return $result;
    }
}
