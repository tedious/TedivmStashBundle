<?php

/*
 * This file is part of the StashBundle package.
 *
 * (c) Josh Hall-Bachner <jhallbachner@gmail.com>
 * (c) Robert Hafner <tedivm@tedivm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tedivm\StashBundle\Service;
use Stash\DriverList;
use Stash\Interfaces\DriverInterface;
use Stash\Pool;

/**
 * Class CacheService
 * @package Tedivm\StashBundle\Service
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class CacheService extends Pool
{

    /**
     * {@inheritdoc}
     */
    protected $itemClass = '\Tedivm\StashBundle\Service\CacheItem';

    /**
     * @var CacheTracker|null
     */
    protected $tracker;

    /**
     * Constructs the cache holder. Parameter is a Stash driver which is dynamically injected at service creation.
     *
     * @param string                            $name    Used to name and prefix the cache to avoid cache collisions across installs
     * @param \Stash\Interfaces\DriverInterface $driver
     * @param CacheTracker|null                 $tracker
     */
    public function __construct($name, DriverInterface $driver = null, CacheTracker $tracker = null)
    {
        $this->tracker = $tracker;
        $this->setNamespace($name);

        if (isset($driver)) {
           $this->setDriver($driver);
        }

        parent::__construct($driver);
    }

    /**
     * {@inheritdoc}
     */
    public function getItem()
    {
        $args = func_get_args();

        // check to see if a single array was used instead of multiple arguments
        if(count($args) == 1 && is_array($args[0]))
            $args = $args[0];

        /** @var CacheItem $item */
        $item = parent::getItem($args);

        if (isset($this->tracker)) {
           $item->setCacheTracker($this->tracker);
        }

        return $item;
    }

    /**
     * {@inheritdoc}
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
     * Returns the current list of drivers that the system is able to use.
     *
     * @return array
     */
    public function getDrivers()
    {
        return DriverList::getAvailableDrivers();
    }

    /**
     * Returns the current tracker.
     *
     * @return CacheTracker|false
     */
    public function getTracker()
    {
        return isset($this->tracker) ? $this->tracker : false;
    }
}
