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
    protected $cache;
    protected $logger;

    /**
     * Constructs the cache holder. Parameter is a Stash handler which is dynamically injected at service creation.
     *
     * @param StashHandler $handler
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
