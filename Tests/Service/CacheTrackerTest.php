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

/**
 * Class CacheTrackerTest
 * @package Tedivm\StashBundle\Tests\Service
 */
class CacheTrackerTest extends \PHPUnit_Framework_TestCase
{
    protected $testClass = '\Tedivm\StashBundle\Service\CacheTracker';

    /**
     * @param  string                                     $name
     * @return '\Tedivm\StashBundle\Service\CacheTracker'
     */
    public function testConstruct($name = 'test')
    {
        $tracker = new $this->testClass($name);
        $this->assertInstanceOf($this->testClass, $tracker);

        return $tracker;
    }

    public function testTrackRequest()
    {

    }

    public function testGetName()
    {
        $tracker = $this->testConstruct();
        $this->assertEquals('test', $tracker->getName(), 'Tracker knows it\'s name');

        $tracker = $this->testConstruct('test2');
        $this->assertEquals('test2', $tracker->getName(), 'Tracker knows it\'s name');
    }

    public function testGetCalls()
    {
        $tracker = $this->getPopulatedTracker();

        $this->assertEquals(6, $tracker->getCalls(), 'Tracker counts calls sent to it.');

        $tracker->trackRequest('Key7', false, 'Value7');

        $this->assertEquals(7, $tracker->getCalls(), 'Tracker counts calls sent to it.');

        $tracker->trackRequest('Key7', false, 'Value7');

        $this->assertEquals(8, $tracker->getCalls(), 'Tracker counts calls sent to it with duplicate keys.');

    }

    public function testGetHits()
    {
        $tracker = $this->getPopulatedTracker();

        $this->assertEquals(3, $tracker->getHits(), 'Tracker counts hits sent to it.');

        $tracker->trackRequest('Key7', false, 'Value7');

        $this->assertEquals(3, $tracker->getHits(), 'Tracker does not count misses as hits.');

        $tracker->trackRequest('Key7', true, 'Value7');

        $this->assertEquals(4, $tracker->getHits(), 'Tracker increments hits when sent them.');

    }

    public function testGetQueries()
    {
        $tracker = $this->getPopulatedTracker();

        $queries = $tracker->getQueries();
        $this->assertCount(6, $queries, 'Tracker returns queries passed to it.');

        $tracker->trackRequest('Key6', false, 'Value6');
        $queries = $tracker->getQueries();
        $this->assertCount(7, $queries, 'Tracker returns queries with duplicate keys.');

        $data = $this->getData();

        foreach ($data as $index => $datum) {
            $query = $queries[$index];
            $expectedTruth = $datum[1] ? 'true' : 'false';
            $this->assertEquals($datum[0], $query['key'], 'getQueries returns key for data example ' . $index);
            $this->assertEquals($expectedTruth, $query['hit'], 'getQueries returns hit status as string for data example ' . $index);
            $this->assertEquals($datum[3], $query['value'], 'getQueries returns value for data example ' . $index);
        }

        $tracker = $this->testConstruct('unpopulated');

        $tracker->enableQueryLogging(false);
        $tracker = $this->getPopulatedTracker($tracker);
        $this->assertCount(0, $tracker->getQueries(), 'Tracker does not track queries when tracking is disabled');
    }

    protected function getPopulatedTracker($tracker = null)
    {
        if (is_null($tracker)) {
            $tracker = $this->testConstruct('populated');
        }

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
