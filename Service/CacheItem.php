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
     * @var null|CacheTracker
     */
    protected $tracker;

    public function setCacheTracker(CacheTracker $tracker)
    {
        $this->tracker = $tracker;
    }

    public function get($invalidation = 0, $arg = null, $arg2 = null)
    {
        $result = parent::get($invalidation, $arg, $arg2);

        if (isset($this->tracker)) {
            $miss = $this->isMiss();
            $key = $this->getKey();
            $this->tracker->trackRequest($key, !($miss), $result);
        }

        return $result;

    }

}
