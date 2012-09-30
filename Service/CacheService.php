<?php

namespace Tedivm\StashBundle\Service;
use Stash\Cache as StashCache;
use Stash\Handlers;

/**
 * Simple result-object provider for the Stash class.
 *
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 */
class CacheService
{
    protected $name;
    protected $handler;
    protected $key;

    protected $logger;

    /**
     * Constructs the cache holder. Parameter is a Stash handler which is dynamically injected at service creation.
     *
     * @param StashHandler $handler
     */
    public function __construct($name, $handler, $logger = null)
    {
        $this->name = $name;
        $this->handler = $handler;
        $this->key = '@@_' . $name . '_@@';

        $this->logger = $logger;
    }

    /**
     * Returns a Stash caching object for the specified key. The key can be either a series of string arguments,
     * or an array.
     *
     * @param array|string $key, $key, $key...
     */
    public function get()
    {
        $args = func_get_args();

        // check to see if a single array was used instead of multiple arguments
        if(count($args) == 1 && is_array($args[0]))
            $args = $args[0];

        array_unshift($args, $this->key);

        $handler = (isset($this->handler)) ? $this->handler : null;
        $cache = new StashCache($handler);
        $stash = new CacheResultObject($cache, $this->logger);

        if(count($args) > 0)
            $stash->setupKey($args);

        return $stash;
    }

    /**
     * Clears the cache for the key, or if none is specified clears the entire cache. The key can be either
     * a series of string arguments, or an array.
     *
     * @param null|string|array $key, $key, $key...
     */
    public function clear()
    {
        $stash = $this->getCache(func_get_args());
        return $stash->clear();
    }

    /**
     * Purges the cache of all stale or obsolete objects, as well as other maintenance tasks specified by the
     * back end caching system. This operation has the potential to be very long running.
     *
     * @return bool
     */
    public function purge()
    {
        $stash = $this->getCache();
        return $stash->purge();
    }

    /**
     * Returns the current list of handlers that the system is able to use.
     *
     * @return array
     */
    public function getHandlers()
    {
        return Handlers::getHandlers();
    }
}
