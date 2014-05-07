<?php

namespace Tedivm\StashBundle\Tests\Service;

use Tedivm\StashBundle\Service\CacheService;
use Stash\Driver\Ephemeral;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    protected $handler;
    protected $service;

    public static function setUpBeforeClass()
    {
        define('TESTING', true);
    }

    protected function setUp()
    {
        $this->handler = new Ephemeral(array());
    }

    protected function getCacheService($name)
    {
        return new CacheService($name, $this->handler);
    }

    public function testCache()
    {
        $service = $this->getCacheService('first');

        $this->runCacheCycle($service, 'one', true);
        $this->runCacheCycle($service, 'two', true);

        $this->runCacheCycle($service, 'one', false);
        $this->runCacheCycle($service, 'two', false);

        $service->clear('test', 'key', 'one');

        $this->runCacheCycle($service, 'one', true);
        $this->runCacheCycle($service, 'two', false);

        $service->clear();

        $this->runCacheCycle($service, 'one', true);
        $this->runCacheCycle($service, 'two', true);
    }

    public function testTwoServices()
    {
        $service1 = $this->getCacheService('first');
        $service2 = $this->getCacheService('second');

        $this->runCacheCycle($service1, 'one', true);
        $this->runCacheCycle($service2, 'two', true);

        $this->runCacheCycle($service2, 'one', true);
        $this->runCacheCycle($service1, 'two', true);

        $this->runCacheCycle($service1, 'one', false);
        $this->runCacheCycle($service2, 'two', false);

        $this->runCacheCycle($service2, 'one', false);
        $this->runCacheCycle($service1, 'two', false);
    }

    public function testGetItemIterator()
    {
        $keys = array('test/key/one', 'test/key/two', 'test/key/three', 'test/key/four');
        $values = array('uno', 'dos', 'tres', 'quattro');

        $service = $this->getCacheService('first');

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
        $cache = $service->getItem('test', 'key', $num);
        $data = $cache->get();
        $this->assertEquals($ismiss, $cache->isMiss());

        $this->assertTrue($cache->set('testkey'.$num));

        $cache = $service->getItem('test', 'key', $num);
        $data = $cache->get();
        $this->assertFalse($cache->isMiss());
        $this->assertEquals('testkey'.$num, $data);
    }

}
