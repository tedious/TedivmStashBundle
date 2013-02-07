<?php

namespace Tedivm\StashBundle\Adapters;
use Stash\Item as StashItem;
use Stash\Drivers;
use Doctrine\Common\Cache\Cache as DoctrineCacheInterface;

class DoctrineAdapter implements DoctrineCacheInterface
{
    protected $cacheService;

    protected $namespace = '';

    protected $caches = array();

    public function __construct($cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

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
            $cache = $this->cacheService->get($id);
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

        $this->caches[$id] = $this->cacheService->get($id);

        return $this->caches[$id]->isMiss();
    }

    /**
     * {@inheritDoc}
     */
    public function save($id, $data, $lifeTime = 0)
    {
        $id = $this->normalizeId($id);

        $cache = $this->cacheService->get($id);

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

    public function flushAll()
    {
        $this->delete('');
    }

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