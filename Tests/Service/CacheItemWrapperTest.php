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

use Stash\Interfaces\ItemInterface;
use Tedivm\StashBundle\Service\CacheItemWrapper;
use Tedivm\StashBundle\Service\CacheTracker;

/**
 * Class CacheItemWrapperTest
 * @package Tedivm\StashBundle\Tests\Service
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class CacheItemWrapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|ItemInterface */
    private $wrappedItem;
    /** @var CacheItemWrapper */
    private $itemWrapper;

    public function testSetPool()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|\Stash\Interfaces\PoolInterface $pool */
        $pool = $this->getMock('\Stash\Interfaces\PoolInterface');
        $this->wrappedItem
            ->expects(static::once())
            ->method('setPool')
            ->with(static::equalTo($pool));

        $this->itemWrapper->setPool($pool);
    }

    public function testDisable()
    {
        $this->wrappedItem
            ->expects(static::once())
            ->method('disable')
            ->willReturn(true);

        static::assertTrue($this->itemWrapper->disable());
    }

    public function testSetKey()
    {
        $key = [uniqid('test-key-', true)];
        $namespace = uniqid('test-namespace-', true);
        $this->wrappedItem
            ->expects(static::once())
            ->method('setKey')
            ->with(
                static::equalTo($key),
                static::equalTo($namespace)
            );

        $this->itemWrapper->setKey($key, $namespace);
    }

    /**
     * @depends testSetKey
     */
    public function testGetKey()
    {
        $key = uniqid('test-key-', true);
        $this->wrappedItem
            ->expects(static::once())
            ->method('getKey')
            ->willReturn($key);

        static::assertEquals($key, $this->itemWrapper->getKey());
    }

    public function testClear()
    {
        $this->wrappedItem
            ->expects(static::once())
            ->method('clear')
            ->willReturn(true);

        static::assertTrue($this->itemWrapper->clear());
    }

    public function testIsMiss()
    {
        $this->wrappedItem
            ->expects(static::once())
            ->method('isMiss')
            ->willReturn(true);

        static::assertTrue($this->itemWrapper->isMiss());
    }

    public function testIsHit()
    {
        $this->wrappedItem
            ->expects(static::once())
            ->method('isHit')
            ->willReturn(true);

        static::assertTrue($this->itemWrapper->isHit());
    }

    public function testExpiresAfter()
    {
        $time = rand(1,100);

        $this->wrappedItem
            ->expects(static::once())
            ->method('expiresAfter')
            ->with(static::equalTo($time))
            ->willReturnSelf();

        static::assertInstanceOf('Stash\Interfaces\ItemInterface', $this->itemWrapper->expiresAfter($time));
    }

    public function testExpiresAt()
    {
        $this->wrappedItem
            ->expects(static::once())
            ->method('expiresAt')
            ->with(static::isNull())
            ->willReturnSelf();

        static::assertInstanceOf('Stash\Interfaces\ItemInterface', $this->itemWrapper->expiresAt(null));
    }

    public function testSave()
    {
        $this->wrappedItem
            ->expects(static::once())
            ->method('save')
            ->willReturn(true);

        static::assertTrue($this->itemWrapper->save());
    }

    public function testLock()
    {
        $ttl = mt_rand(1, 100);

        $this->wrappedItem
            ->expects(static::once())
            ->method('lock')
            ->with(static::equalTo($ttl))
            ->willReturn(true);

        static::assertTrue($this->itemWrapper->lock($ttl));
    }

    public function testSet()
    {
        $data = uniqid('test-data-', true);

        $this->wrappedItem
            ->expects(static::once())
            ->method('set')
            ->with(static::equalTo($data))
            ->willReturnSelf();

        static::assertInstanceOf('Stash\Interfaces\ItemInterface', $this->itemWrapper->set($data));
    }

    public function testExtend()
    {
        $ttl = mt_rand(1, 100);

        $this->wrappedItem
            ->expects(static::once())
            ->method('extend')
            ->with(static::equalTo($ttl))
            ->willReturn(true);

        static::assertTrue($this->itemWrapper->extend($ttl));
    }

    public function testIsDisabled()
    {
        $this->wrappedItem
            ->expects(static::once())
            ->method('isDisabled')
            ->willReturn(false);

        static::assertFalse($this->itemWrapper->isDisabled());
    }

    public function testSetLogger()
    {
        /** @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->getMock('\Psr\Log\LoggerInterface');

        $this->wrappedItem
            ->expects(static::once())
            ->method('setLogger')
            ->with(static::equalTo($logger))
            ->willReturn(true);

        static::assertTrue($this->itemWrapper->setLogger($logger));
    }

    public function testGetCreation()
    {
        $dateTime = new \DateTime();

        $this->wrappedItem
            ->expects(static::once())
            ->method('getCreation')
            ->willReturn($dateTime);

        static::assertEquals($dateTime, $this->itemWrapper->getCreation());
    }

    public function testGetExpiration()
    {
        $dateTime = new \DateTime();

        $this->wrappedItem
            ->expects(static::once())
            ->method('getExpiration')
            ->willReturn($dateTime);

        static::assertEquals($dateTime, $this->itemWrapper->getExpiration());
    }

    public function testSetCacheTracker()
    {
        $tracker = new CacheTracker('test');
        $this->itemWrapper->setCacheTracker($tracker);

        static::assertAttributeEquals($tracker, 'tracker', $this->itemWrapper,
            'SetTracker sets the tracker as expected.');
    }

    /**
     * @depends testIsMiss
     * @depends testGetKey
     * @depends testSetCacheTracker
     */
    public function testGetTracking()
    {
        $tracker = new CacheTracker('test');
        $this->itemWrapper->setCacheTracker($tracker);

        $data = uniqid('test-data-', true);
        $this->wrappedItem
            ->expects(static::once())
            ->method('get')
            ->with(static::equalTo(0), static::isNull(), static::isNull())
            ->willReturn($data);
        $this->wrappedItem->expects(static::once())->method('get')->willReturn(1);
        $this->wrappedItem->expects(static::once())->method('isMiss')->willReturn(true);
        $this->wrappedItem->expects(static::once())->method('getKey')->willReturn('test-key');

        static::assertEquals($data, $this->itemWrapper->get());

        static::assertEquals(1, $tracker->getCalls(), 'Item tracked a call');
        static::assertEquals(0, $tracker->getHits(), 'Item tracked a call');
        static::assertCount(1, $tracker->getQueries(), 'Item tracked a query');
    }

    protected function setUp()
    {
        $this->wrappedItem = $this->getMock('\Stash\Interfaces\ItemInterface');
        $this->itemWrapper = new CacheItemWrapper($this->wrappedItem);
    }
}
