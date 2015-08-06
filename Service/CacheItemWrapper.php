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
use Stash\Interfaces\ItemInterface;
use Stash\Interfaces\PoolInterface;

/**
 * Class CacheItem
 * @package Tedivm\StashBundle\Service
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class CacheItemWrapper implements ItemInterface
{
    /**
     * @var null|CacheTracker
     */
    protected $tracker;

    /** @var ItemInterface */
    protected $wrappedItem;

    /**
     * CacheItemWrapper constructor.
     * @param ItemInterface $wrappedItem
     */
    public function __construct(ItemInterface $wrappedItem)
    {
        $this->wrappedItem = $wrappedItem;
    }

    /**
     * Enables tracking of hits. Typically called by Service Factory
     *
     * @param CacheTracker $tracker
     */
    public function setCacheTracker(CacheTracker $tracker)
    {
        $this->tracker = $tracker;
    }

    /**
     * {@inheritdoc}
     */
    public function get($invalidation = 0, $arg = null, $arg2 = null)
    {
        $result = $this->wrappedItem->get($invalidation, $arg, $arg2);

        if (isset($this->tracker)) {
            $miss = $this->isMiss();
            $key = $this->getKey();
            $this->tracker->trackRequest($key, !($miss), $result);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setPool(PoolInterface $driver)
    {
        $this->wrappedItem->setPool($driver);
    }

    /**
     * {@inheritdoc}
     */
    public function setKey(array $key, $namespace = null)
    {
        $this->wrappedItem->setKey($key, $namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function disable()
    {
        return $this->wrappedItem->disable();
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->wrappedItem->getKey();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->wrappedItem->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function isMiss()
    {
        return $this->wrappedItem->isMiss();
    }

    /**
     * {@inheritdoc}
     */
    public function lock($ttl = null)
    {
        return $this->wrappedItem->lock($ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function set($data, $ttl = null)
    {
        return $this->wrappedItem->set($data, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function extend($ttl = null)
    {
        return $this->wrappedItem->extend($ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function isDisabled()
    {
        return $this->wrappedItem->isDisabled();
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger($logger)
    {
        return $this->wrappedItem->setLogger($logger);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreation()
    {
        return $this->wrappedItem->getCreation();
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiration()
    {
        return $this->wrappedItem->getExpiration();
    }
}
