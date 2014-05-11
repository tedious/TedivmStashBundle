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
use Stash\Item;

/**
 * Class CacheItem
 * @package Tedivm\StashBundle\Service
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class CacheItem extends Item
{

    /**
     * @var null|CacheTracker
     */
    protected $tracker;

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
        $result = parent::get($invalidation, $arg, $arg2);

        if (isset($this->tracker)) {
            $miss = $this->isMiss();
            $key = $this->getKey();
            $this->tracker->trackRequest($key, !($miss), $result);
        }

        return $result;
    }

}
