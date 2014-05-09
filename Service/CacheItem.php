<?php

namespace Tedivm\StashBundle\Service;
use Stash\Item;

/**
 * Simple result-object provider for the Stash class.
 *
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 */
class CacheItem extends Item
{

    /**
     * @var null|CacheLogger
     */
    protected $cacheLogger;

    public function setCacheLogger(CacheLogger $logger)
    {
        $this->cacheLogger = $logger;
    }

    public function get($invalidation = 0, $arg = null, $arg2 = null)
    {
        $result = parent::get($invalidation, $arg, $arg2);

        if (isset($this->logger)) {
            $miss = $this->isMiss();
            $key = $this->getKey();
            $this->logger->logRequest($key, !($miss), $result);
        }

        return $result;

    }

}
