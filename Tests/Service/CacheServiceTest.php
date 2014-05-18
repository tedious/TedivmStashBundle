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

use Tedivm\StashBundle\Service\CacheService;
use Tedivm\StashBundle\Service\CacheTracker;
use Stash\Driver\Ephemeral;
use Stash\DriverList;

/**
 * Class CacheServiceTest
 * @package Tedivm\StashBundle\Tests\Service
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class CacheServiceTest extends \Stash\Test\AbstractPoolTest
{
    protected $serviceClass = '\Tedivm\StashBundle\Service\CacheService';

    protected function getCacheService($name = 'test')
    {
        return new $this->serviceClass($name);
    }

    public function testCacheServiceConstruct()
    {
        $driver = new Ephemeral();
        $tracker = new CacheTracker('test');
        $service = new $this->serviceClass('test', $driver, $tracker);

        $this->assertAttributeEquals($driver, 'driver', $service, 'Constructor sets driver when passed.');
        $this->assertAttributeEquals($tracker, 'tracker', $service, 'Constructor sets tracker when passed.');
        $this->assertEquals('test', $service->getNamespace(), 'Constructor sets name as namespace.');
    }

    public function testGetItemWithTracker()
    {
        $driver = new Ephemeral();
        $tracker = new CacheTracker('test');
        $service = new $this->serviceClass('test', $driver, $tracker);

        $item = $service->getItem('fakeItem');
        $this->assertAttributeEquals($tracker, 'tracker', $item, 'Item gets Tracker from Service on creation.');
    }

    public function testGetTracker()
    {
        $driver = new Ephemeral();
        $tracker = new CacheTracker('test');
        $service = new $this->serviceClass('test', $driver, $tracker);
        $this->assertEquals($tracker, $service->getTracker(), 'Service returns it\'s tracker..');
    }

    public function testGetDrivers()
    {
        $service = $this->getCacheService();
        $this->assertEquals(DriverList::getAvailableDrivers(), $service->getDrivers(), 'Service available drivers');
    }

    public function testCacheService()
    {
        $service = $this->getCacheService('first');
        $service->setDriver(new Ephemeral());

        $this->runCacheCycle($service, 'one', true);
        $this->runCacheCycle($service, 'two', true);

        $this->runCacheCycle($service, 'one', false);
        $this->runCacheCycle($service, 'two', false);

        $this->assertTrue($service->clear('test', 'key', 'one'));

        $this->runCacheCycle($service, 'one', true);
        $this->runCacheCycle($service, 'two', false);

        $this->assertTrue($service->clear());

        $this->runCacheCycle($service, 'one', true);
        $this->runCacheCycle($service, 'two', true);
    }

    public function testTwoServices()
    {
        $driver = new Ephemeral();
        $service1 = $this->getCacheService('first');
        $service1->setDriver($driver);
        $service2 = $this->getCacheService('second');
        $service2->setDriver($driver);

        $this->runCacheCycle($service1, 'one', true);
        $this->runCacheCycle($service2, 'two', true);

        $this->runCacheCycle($service2, 'one', true);
        $this->runCacheCycle($service1, 'two', true);

        $this->runCacheCycle($service1, 'one', false);
        $this->runCacheCycle($service2, 'two', false);

        $this->runCacheCycle($service2, 'one', false);
        $this->runCacheCycle($service1, 'two', false);
    }

    public function testServiceGetItemIterator()
    {
        $keys = array('test/key/one', 'test/key/two', 'test/key/three', 'test/key/four');
        $values = array('uno', 'dos', 'tres', 'quattro');

        $service = $this->getCacheService('first');
        $service->setDriver(new Ephemeral());

        $iterator = $service->getItemIterator($keys);
        $setvalues = $values;

        foreach ($iterator as $item) {
            $this->assertTrue($item->isMiss());
            $val = array_shift($setvalues);
            $item->set($val);
            $this->assertEquals($val, $item->get());
        }

        $iterator2 = $service->getItemIterator($keys);
        $getvalues = $values;

        foreach ($iterator2 as $item) {
            $this->assertFalse($item->isMiss());
            $val = array_shift($getvalues);
            $this->assertEquals($val, $item->get());
        }
    }

    protected function runCacheCycle($service, $num, $ismiss)
    {
        $key = array('test', 'key', $num);
        $testData = 'testkey' . $num;

        $item = $service->getItem($key);
        $data = $item->get();
        $this->assertEquals($ismiss, $item->isMiss());

        $this->assertTrue($item->set($testData));

        $item = $service->getItem($key);
        $data = $item->get();
        $this->assertFalse($item->isMiss());
        $this->assertEquals($testData, $data);
    }

}
