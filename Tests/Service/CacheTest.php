<?php

namespace Tedivm\StashBundle\Tests\Service;

use Tedivm\StashBundle\Service\CacheService;
use Stash\Driver\Ephemeral;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    protected $handler;

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
