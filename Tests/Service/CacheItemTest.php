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

namespace Tedivm\StashBundle\Tests\Service;

use Tedivm\StashBundle\Service\CacheTracker;

/**
 * Class CacheItemTest
 * @package Tedivm\StashBundle\Tests\Service
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class CacheItemTest extends \Stash\Test\AbstractItemTest
{
    protected $itemClass = '\Tedivm\StashBundle\Service\CacheItem';

    public function testSetCacheTracker()
    {
        $tracker = new CacheTracker('test');
        $item = $this->getItem();
        $item->setCacheTracker($tracker);

        $this->assertAttributeEquals($tracker, 'tracker', $item, 'SetTracker sets the tracker as expected.');
    }

    public function testGetTracking()
    {
        $tracker = new CacheTracker('test');
        $item = $this->getItem();
        $item->setCacheTracker($tracker);

        $item->get();

        $this->assertEquals(1, $tracker->getCalls(), 'Item tracked a call');
        $this->assertEquals(0, $tracker->getHits(), 'Item tracked a call');
        $this->assertCount(1, $tracker->getQueries(), 'Item tracked a query');
    }
}
