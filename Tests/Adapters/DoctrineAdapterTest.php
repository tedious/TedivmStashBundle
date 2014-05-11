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

namespace Tedivm\StashBundle\Tests\Adapters;

use Stash\Driver\Ephemeral;
use Tedivm\StashBundle\Service\CacheService;
use Tedivm\StashBundle\Service\CacheTracker;
use Tedivm\StashBundle\Adapters\DoctrineAdapter;
use Tedivm\StashBundle\Tests\ThirdParty\Doctrine\CacheTest;

/**
 * Class DoctrineAdapterTest
 * @package Tedivm\StashBundle\Tests\Adapters
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class DoctrineAdapterTest extends CacheTest
{
    protected $__driver;

    public function SetUp()
    {
        if (!interface_exists('\\Doctrine\\Common\\Cache\\Cache')) {
            $this->markTestSkipped('Test requires DoctrineCache');
        }
    }

    public function testGetStatsWithoutTracker()
    {
        if (!isset($this->__driver)) {
            $this->__driver = new Ephemeral(array());
        }

        $service = new CacheService('test', new Ephemeral(array()));
        $adaptor = new DoctrineAdapter($service);
        $stats = $adaptor->getStats();

        $keys = array('memory_usage', 'memory_available', 'uptime', 'hits', 'misses');

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $stats, 'getStats has ' . $key . ' key even without tracker.');
            $this->assertEquals('NA', $stats[$key], 'getStats returns NA for key ' . $key . ' without tracker.');
        }
    }

    public function testGetNamespace()
    {
        $service = $this->_getCacheDriver();
        $this->assertEquals('', $service->getNamespace(), 'getNamespace returns empty string when no namespace is set.');
        $service->setNamespace('TestNameSpace');
        $this->assertEquals('TestNameSpace', $service->getNamespace(), 'getNamespace returns set namespace.');
    }

    /**
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    protected function _getCacheDriver()
    {
        if (!isset($this->__driver)) {
            $this->__driver = new Ephemeral(array());
        }

        $service = new CacheService('test', $this->__driver, new CacheTracker('test'));
        $adaptor = new DoctrineAdapter($service);

        return $adaptor;
    }

    /*
     * These tests were originally put in the Doctrine provider to enforce
     * bad/buggy behavior. The Stash Doctrine provider *does not* serve
     * stale data, but works as it would be expected to. As such I've
     * removed the test cases that attempt to enforce that.
     *
     * The only difference between this and the Doctrine behavior is that
     * Stash will not serve stale data in cases were Doctrine's cache might.
     */

    public function testDeleteAllAndNamespaceVersioningBetweenCaches()
    {

    }

    public function testFlushAllAndNamespaceVersioningBetweenCaches()
    {

    }

}
