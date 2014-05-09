<?php

namespace Tedivm\StashBundle\Tests\Service;

use Tedivm\StashBundle\Service\CacheService;
use Stash\Driver\Ephemeral;

class CacheServiceTest extends \Stash\Test\AbstractPoolTest
{
    protected $itemClass = '\Tedivm\StashBundle\Service\CacheService';

    protected function getCacheService($name)
    {
        return new $this->itemClass($name);
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
