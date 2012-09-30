<?php

namespace Tedivm\StashBundle\Adapters;
use Stash\Cache as StashCache;
use Stash\Handlers;
use Doctrine\Common\Cache\Cache as DoctrineCacheInterface;

class DoctrineAdapter implements DoctrineCacheInterface
{
    protected $cacheService;

    protected $caches = array();

    public function __construct($cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($id)
    {
        if(isset($this->caches[$id])) {
            $cache = $this->caches[$id];
            unset($this->caches[$id]);
        } else {
            $cache = $this->cacheService->get($id);
        }

        if($cache->isMiss()) {
            return false;
        } else {
            return $cache->get();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function contains($id)
    {
        $this->caches[$id] = $this->cacheService->get($id);

        return $this->caches[$id]->isMiss();
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        $cache = $this->cacheService->get($id);

        return $cache->set($data, $lifeTime);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($id)
    {
        $cache = $this->cacheService->get($id);
        return $cache->clear();
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



}