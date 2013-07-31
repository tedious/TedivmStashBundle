<?php

namespace Tedivm\StashBundle\Adapters;
use Stash\Item as StashItem;
use Stash\Drivers;
use Doctrine\Common\Cache\Cache as DoctrineCacheInterface;

class DoctrineAdapter implements DoctrineCacheInterface
{
    /**
     * The cache service being wrapped.
     *
     * @var \Stash\Pool
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

        if(isset($this->caches[$id])) {
            $cache = $this->caches[$id];
            unset($this->caches[$id]);
        } else {
            $cache = $this->cacheService->getItem($id);
        }

        $value = $cache->get();
        if($cache->isMiss()) {
            return false;
        } else {
            return $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id)
    {
        $id = $this->normalizeId($id);

        $this->caches[$id] = $this->cacheService->getItem($id);

        return $this->caches[$id]->isMiss();
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        $id = $this->normalizeId($id);

        $cache = $this->cacheService->getItem($id);

        return $cache->set($data, $lifeTime);
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
        $logger = $this->cacheService->getLogger();
        $stats['hits'] = $logger->getHits();
        $stats['misses'] = $logger->getCalls() - $stats['hits'];

        return $stats;
    }

    /**
     * Deletes all keys.
     */
    public function flushAll()
    {
        $this->delete('');
    }

    /**
     * Standardizes the cache key id in order to separate cache items by namespace.
     *
     * @param $id
     * @return string
     */
    protected function normalizeId($id)
    {
        if(isset($this->namespace)) {
            $id = sprintf('zz_%s_zz/%s', $this->namespace, $id);
            $id = trim($id, '/');
            return $id;
        } else {
            return $id;
        }
    }
}