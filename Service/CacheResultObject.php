<?php

namespace Tedivm\StashBundle\Service;
use Stash\Handlers;

/**
 * Simple result-object provider for the Stash class.
 *
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 */
class CacheResultObject
{
    /**
     * @var \Stash\Cache
     */
    protected $cache;

    /**
     * @var null|CacheLogger
     */
    protected $logger;

    /**
     * Constructs the CacheResultObject, wraps Cache object to perform logging.
     *
     * @param \Stash\Cache $cache
     * @param CacheLogger $logger
     */
    public function __construct($cache, $logger = null)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function __call($name, $args)
    {
        if($name === 'get') {
            return $this->getAndLog($args);
        } else {
            return call_user_func_array(array($this->cache, $name), $args);
        }
    }

    protected function getAndLog($args)
    {
        $result = call_user_func_array(array($this->cache, 'get'), $args);
        $miss = $this->cache->isMiss();
        $key = $this->cache->getKey();

        if(isset($this->logger)) {
            $this->logger->logRequest($key, !($miss), $result);
        }

        return $result;
    }
}
