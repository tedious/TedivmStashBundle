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
use Doctrine\Common\Cache\Cache;
use ArrayObject;

class DoctrineAdapterTest extends \PHPUnit_Framework_TestCase
{
    protected $__driver;

    /**
     * @dataProvider provideCrudValues
     */
    public function testBasicCrudOperations($value)
    {
        $cache = $this->_getCacheDriver();

        // Test saving a value, checking if it exists, and fetching it back
        $this->assertTrue($cache->save('key', 'value'));
        $this->assertTrue($cache->contains('key'));
        $this->assertEquals('value', $cache->fetch('key'));

        // Test updating the value of a cache entry
        $this->assertTrue($cache->save('key', 'value-changed'));
        $this->assertTrue($cache->contains('key'));
        $this->assertEquals('value-changed', $cache->fetch('key'));

        // Test deleting a value
        $this->assertTrue($cache->delete('key'));
        $this->assertFalse($cache->contains('key'));
    }

    public function provideCrudValues()
    {
        return array(
            'array' => array(array('one', 2, 3.0)),
            'string' => array('value'),
            'integer' => array(1),
            'float' => array(1.5),
            'object' => array(new ArrayObject()),
        );
    }

    public function testDeleteAll()
    {
        $cache = $this->_getCacheDriver();

        $this->assertTrue($cache->save('key1', 1));
        $this->assertTrue($cache->save('key2', 2));
        $this->assertTrue($cache->deleteAll());
        $this->assertFalse($cache->contains('key1'));
        $this->assertFalse($cache->contains('key2'));
    }

    public function testFlushAll()
    {
        $cache = $this->_getCacheDriver();

        $this->assertTrue($cache->save('key1', 1));
        $this->assertTrue($cache->save('key2', 2));
        $this->assertTrue($cache->flushAll());
        $this->assertFalse($cache->contains('key1'));
        $this->assertFalse($cache->contains('key2'));
    }

    public function testNamespace()
    {
        $cache = $this->_getCacheDriver();

        $cache->setNamespace('ns1_');

        $this->assertTrue($cache->save('key1', 1));
        $this->assertTrue($cache->contains('key1'));

        $cache->setNamespace('ns2_');

        $this->assertFalse($cache->contains('key1'));
    }

    public function testDeleteAllNamespace()
    {
        $cache = $this->_getCacheDriver();

        $cache->setNamespace('ns1');
        $this->assertFalse($cache->contains('key1'));
        $cache->save('key1', 'test');
        $this->assertTrue($cache->contains('key1'));

        $cache->setNamespace('ns2');
        $this->assertFalse($cache->contains('key1'));
        $cache->save('key1', 'test');
        $this->assertTrue($cache->contains('key1'));

        $cache->setNamespace('ns1');
        $this->assertTrue($cache->contains('key1'));
        $cache->deleteAll();
        $this->assertFalse($cache->contains('key1'));

        $cache->setNamespace('ns2');
        $this->assertTrue($cache->contains('key1'));
        $cache->deleteAll();
        $this->assertFalse($cache->contains('key1'));
    }

    /**
     * @group DCOM-43
     */
    public function testGetStats()
    {
        $cache = $this->_getCacheDriver();
        $stats = $cache->getStats();

        $this->assertArrayHasKey(Cache::STATS_HITS, $stats);
        $this->assertArrayHasKey(Cache::STATS_MISSES, $stats);
        $this->assertArrayHasKey(Cache::STATS_UPTIME, $stats);
        $this->assertArrayHasKey(Cache::STATS_MEMORY_USAGE, $stats);
        $this->assertArrayHasKey(Cache::STATS_MEMORY_AVAILABLE, $stats);
    }

    public function testFetchMissShouldReturnFalse()
    {
        $cache = $this->_getCacheDriver();

        /* Ensure that caches return boolean false instead of null on a fetch
         * miss to be compatible with ORM integration.
         */
        $result = $cache->fetch('nonexistent_key');

        $this->assertFalse($result);
        $this->assertNotNull($result);
    }

    /**
     * Return whether multiple cache providers share the same storage.
     *
     * This is used for skipping certain tests for shared storage behavior.
     *
     * @return boolean
     */
    protected function isSharedStorage()
    {
        return true;
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
