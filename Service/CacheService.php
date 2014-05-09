<?php

namespace Tedivm\StashBundle\Service;
use Stash\Drivers;
use Stash\Interfaces\DriverInterface;
use Stash\Pool;

/**
 * Simple result-object provider for the Stash class.
 *
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 */
class CacheService extends Pool
{

    protected $itemClass = '\Tedivm\StashBundle\Service\CacheItem';

    /**
     * @var CacheLogger|null
     */
    protected $logger;

    /**
     * Constructs the cache holder. Parameter is a Stash handler which is dynamically injected at service creation.
     *
     * @param string                            $name   Used to name and prefix the cache to avoid cache collisions across installs
     * @param \Stash\Interfaces\DriverInterface $driver
     * @param CacheLogger|null                  $logger
     */
    public function __construct($name, DriverInterface $driver, CacheLogger $logger = null)
    {
        $this->logger = $logger;
        $this->setNamespace($name);
        parent::__construct($driver);
    }

    /**
     * Returns a Stash caching object for the specified key. The key can be either a series of string arguments,
     * or an array.
     *
     * @param  mixed     $key,... String Representing the key
     * @return CacheItem
     */
    public function getItem()
    {
        $args = func_get_args();

        // check to see if a single array was used instead of multiple arguments
        if(count($args) == 1 && is_array($args[0]))
            $args = $args[0];

        $item = parent::getItem($args);

        if (isset($this->logger)) {
           $item->setCacheLogger($this->logger);
        }

        return $item;
    }

    /**
     * Clears the cache for the key, or if none is specified clears the entire cache. The key can be either
     * a series of string arguments, or an array.
     *
     * @param mixed $key,... String Representing the key
     */
    public function clear()
    {
        $args = func_get_args();
        if (count($args) === 0) {
            return $this->flush();
        } else {
            $item = $this->getItem($args);

            return $item->clear();
        }
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
     * @return CacheLogger|false
     */
    public function getLogger()
    {
        return isset($this->logger) ? $this->logger : false;
    }
}
