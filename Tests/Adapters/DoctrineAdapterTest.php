<?php
/**
 *
 * This test was copied and modified from the DoctrineCache project.
 *
 * Copyright (c) 2006-2012 Doctrine Project
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Tedivm\StashBundle\Tests\Adapters;

use Stash\Driver\Ephemeral;
use Tedivm\StashBundle\Service\CacheService;
use Tedivm\StashBundle\Service\CacheTracker;
use Tedivm\StashBundle\Adapters\DoctrineAdapter;
use Tedivm\StashBundle\Tests\ThirdParty\Doctrine\CacheTest;

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
