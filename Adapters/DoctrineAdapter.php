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

namespace Tedivm\StashBundle\Adapters;
use Doctrine\Common\Cache\Cache as DoctrineCacheInterface;

/**
 * Class DoctrineAdapter
 * @package Tedivm\StashBundle\Adapters
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class DoctrineAdapter implements DoctrineCacheInterface
{
    /**
     * The cache service being wrapped.
     *
     * @var \Tedivm\StashBundle\Service\CacheService
     */
    protected $cacheService;

    /**
     * The Doctrine Cache namespace being used.
     *
     * @var string
     */
    protected $namespace = '';

    /**
     * Storage for cache items to avoid race conditions between checking and fetching.
     *
     * @var array
     */
    protected $caches = array();

    /**
     * Initializes
     *
     * @param \Tedivm\StashBundle\Service\CacheService $cacheService
     */
    public function __construct($cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Sets the current namespace.
     *
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Returns the current namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($id)
    {
        $id = $this->normalizeId($id);
        $cache = $this->cacheService->getItem($id);
        $value = $cache->get();

        return $cache->isMiss() ? false : $value;
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id)
    {
        $id = $this->normalizeId($id);
        $item = $this->cacheService->getItem($id);

        return !$item->isMiss();
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        /* Stash treats 0 as a 0 second ttl, so we convert to null */
        if ($lifeTime === 0) {
            $lifeTime = null;
        }

        $id = $this->normalizeId($id);
        $item = $this->cacheService->getItem($id);

        return $item->set($data, $lifeTime);

    }

    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        $id = $this->normalizeId($id);

        return $this->cacheService->clear($id);
    }

    public function deleteAll()
    {
        return $this->flushAll();
    }

    /**
     * {@inheritDoc}
     */
    public function getStats()
    {
        $stats = array();
        $stats['memory_usage'] = 'NA';
        $stats['memory_available'] = 'NA';
        $stats['uptime'] = 'NA';

        if ($tracker = $this->cacheService->getTracker()) {
            $stats['hits'] = $tracker->getHits();
            $stats['misses'] = $tracker->getCalls() - $stats['hits'];
        } else {
            $stats['hits'] = 'NA';
            $stats['misses'] = 'NA';
        }

        return $stats;
    }

    /**
     * Deletes all keys.
     */
    public function flushAll()
    {
        return $this->delete('');
    }

    /**
     * Standardizes the cache key id in order to separate cache items by namespace.
     *
     * @param $id
     * @return string
     */
    protected function normalizeId($id)
    {
        $namespace = (isset($this->namespace) && $this->namespace != '') ? $this->namespace : 'default';
        $id = sprintf('zz_%s_zz/%s', $namespace, $id);

        return $id;
    }
}
