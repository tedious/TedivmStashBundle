<?php

namespace Tedivm\StashBundle\Service;
use Stash\Item;
use Stash\Drivers;
use Stash\Driver\DriverInterface;
use Stash\Pool;
use ArrayIterator;

/**
 * Simple result-object provider for the Stash class.
 *
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 */
class CacheService extends Pool
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Stash\Driver\DriverInterface
     */
    protected $driver;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var CacheLogger|null
     */
    protected $logger;

    /**
     * Constructs the cache holder. Parameter is a Stash handler which is dynamically injected at service creation.
     *
     * @param string $name Used to name and prefix the cache to avoid cache collisions across installs
     * @param \Stash\Handler\HandlerInterface $handler
     * @param CacheLogger|null $logger
     */
    public function __construct($name, DriverInterface $driver, CacheLogger $logger = null)
    {
        $this->name = $name;
        $this->driver = $driver;
        $this->key = '@@_' . $name . '_@@';

        $this->pool = new Pool($this->driver);

        $this->logger = $logger;
    }

    /**
     * Returns a Stash caching object for the specified key. The key can be either a series of string arguments,
     * or an array.
     *
     * @param string|array $key, $key, $key...
     * @return \Stash\Item Note: Cache item is wrapped inside CacheResultObject which deals with logging
     */
    public function getItem()
    {
        $args = func_get_args();

        // check to see if a single array was used instead of multiple arguments
        if(count($args) == 1 && is_array($args[0]))
            $args = $args[0];

        array_unshift($args, $this->key);

        $item = $this->pool->getItem($args);

        $stash = new CacheResultObject($item, $this->logger);

        return $stash;
    }

    /**
     * Returns a group of wrapped cache objects as an \Iterator. This duplicates the functionality of the
     * Pool class getItemIterator method, but with wrapped, loggable cache items.
     *
     * @param array $keys
     * @return \Iterator
     */
    public function getItemIterator($keys)
    {
        $items = array();
        foreach($keys as $key)
        {
            $items[] = $this->getItem($key);
        }

         return new ArrayIterator($items);
    }

    /**
     * Clears the cache for the key, or if none is specified clears the entire cache. The key can be either
     * a series of string arguments, or an array.
     *
     * @param null|string|array $key, $key, $key...
     */
    public function clear()
    {
        $args = func_get_args();
        if(count($args) === 0) {
            return $this->pool->flush();
        } else {
            $stash = call_user_func_array(array($this, 'getItem'), $args);
            return $stash->clear();
        }
    }

    /**
     * Purges the cache of all stale or obsolete objects, as well as other maintenance tasks specified by the
     * back end caching system. This operation has the potential to be very long running.
     *
     * @return bool
     */
    public function purge()
    {
        return $this->pool->purge();
    }

    /**
     * Returns the current list of handlers that the system is able to use.
     *
     * @return array
     */
    public function getDrivers()
    {
        return Drivers::getDrivers();
    }

    /**
     * Returns the current logger
     *
     * @return CacheLogger|null
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
