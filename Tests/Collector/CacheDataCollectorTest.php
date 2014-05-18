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

namespace Tedivm\StashBundle\Tests\Collector;

use \Stash\DriverList;
use \Tedivm\StashBundle\Service\CacheTracker as Tracker;

/**
 * Class CacheDataCollectorTest
 * @package Tedivm\StashBundle\Tests\Collector
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class CacheDataCollectorTest extends \PHPUnit_Framework_TestCase
{
    protected $testClass = 'Tedivm\StashBundle\Collector\CacheDataCollector';

    /**
     * @param  string                                           $cacheService
     * @param  array                                            $caches
     * @param  array                                            $options
     * @return \Tedivm\StashBundle\Collector\CacheDataCollector
     */
    public function testConstruct($cacheService = 'default', $caches = array('default'), $options = array('default' => array()))
    {
        $collector = new $this->testClass($cacheService, $caches, $options);
        $this->assertInstanceOf($this->testClass, $collector);

        return $collector;
    }

    public function testAddTracker()
    {
        $collector = $this->testConstruct();

        $tracker = new Tracker('first');
        $collector->addTracker($tracker);
        $this->assertAttributeCount(1, 'trackers', $collector, 'Collector added tracker.');

        $tracker = new Tracker('second');
        $collector->addTracker($tracker);
        $this->assertAttributeCount(2, 'trackers', $collector, 'Collector added additional tracker.');
    }

    public function testGetCalls()
    {
        $collector = $this->getPrimedCollector();

        // 18 == 3 trackers with 6 calls on each.
        $this->assertEquals(18, $collector->getCalls(), 'Get calls returns expected number');
    }

    public function testGetHits()
    {
        $collector = $this->getPrimedCollector();

        // 9 == 3 trackers with 3 hits on each.
        $this->assertEquals(9, $collector->getHits(), 'Get hits returns expected number');
    }

    public function testGetDefault()
    {
        $collector = $this->getPrimedCollector();
        $this->assertEquals('first', $collector->getDefault(), 'Get default returns default cache name');
    }

    public function testGetName()
    {
        $collector = $this->getPrimedCollector();

        $this->assertEquals('stash', $collector->getName(), 'Get name returns "stash"');
    }

    public function testGetCaches()
    {
        $collector = $this->getPrimedCollector();

        $caches = $collector->getCaches();

        $this->assertInternalType('array', $caches, 'getCaches returns array.');

        $this->assertArrayHasKey('first', $caches, 'getCaches returns associative array of caches.');
        $this->assertArrayHasKey('second', $caches, 'getCaches returns associative array of multiple caches.');

        $firstCache = $caches['first'];
        $this->assertArrayHasKey('options', $firstCache, 'getCaches cache array contains options.');
        $this->assertInternalType('array', $firstCache['options'], 'getCaches options are an array.');
        $this->assertArrayHasKey('calls', $firstCache, 'getCaches cache array contains options.');
        $this->assertArrayHasKey('hits', $firstCache, 'getCaches cache array contains options.');
        $this->assertArrayHasKey('queries', $firstCache, 'getCaches cache array contains options.');

        $secondCache = $caches['second'];
        $this->assertArrayHasKey('options', $secondCache, 'getCaches second cache array contains options.');
        $this->assertInternalType('array', $secondCache['options'], 'getCaches second cache options are an array.');
        $this->assertArrayHasKey('calls', $secondCache, 'getCaches second cache array contains options.');
        $this->assertArrayHasKey('hits', $secondCache, 'getCaches second cache array contains options.');
        $this->assertArrayHasKey('queries', $secondCache, 'getCaches second cache array contains options.');
    }

    public function testGetAvailableDrivers()
    {
        $collector = $this->getPrimedCollector();
        $drivers = $collector->getDrivers();
        $this->assertInternalType('array', $drivers, 'getDrivers returns an array');

        $systemDrivers = array_keys(DriverList::getAvailableDrivers());

        $this->assertFalse(in_array('Ephemeral', $drivers), 'getDrivers does not include Ephemeral driver');
        $this->assertFalse(in_array('Composite', $drivers), 'getDrivers does not include Composite driver');
        $this->assertTrue(in_array('FileSystem', $drivers), 'getDrivers does always include FileSystem driver');

        //var_dump($drivers, $systemDrivers);exit();
        foreach ($drivers as $driver) {
            $this->assertTrue(in_array($driver, $systemDrivers),
                'getDrivers returns only registered drivers- Unregistered: ' . $driver);
        }
    }

    protected function getPrimedCollector()
    {
        $collector = $this->getPopulatedCollector();

        $requestMock = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $responseMock = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $collector->collect($requestMock, $responseMock);

        return $collector;
    }

    protected function getPopulatedCollector()
    {
        $services = array('first', 'second', 'third');
        $options = array('first' => array(), 'second' => array(), 'third' => array());
        $collector = $this->testConstruct('first', $services, $options);
        $this->getPopulatedTracker('first');
        $collector->addTracker($this->getPopulatedTracker('first'));
        $collector->addTracker($this->getPopulatedTracker('second'));
        $collector->addTracker($this->getPopulatedTracker('third'));

        return $collector;
    }

    protected function getPopulatedTracker($name)
    {
        $tracker = new Tracker($name);
        $data = $this->getData();
        foreach ($data as $datum) {
            $tracker->trackRequest($datum[0], $datum[1], $datum[2]);
        }

        return $tracker;
    }

    protected function getData()
    {
        return array(
            array('Key1', true, 'Value1', '(string) Value1'),
            array('Key2', false, 2, '(integer) 2'),
            array('Key3', true, array(), "(array) Array\n(\n)\n"),
            array('Key4', false, new \stdClass(), "(object) stdClass Object\n(\n)\n"),
            array('Key5', true, 'Value5', '(string) Value5'),
            array('Key6', false, 'Value6', '(string) Value6'),
        );
    }
}
