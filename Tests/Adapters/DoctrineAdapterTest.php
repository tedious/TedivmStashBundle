<?php

namespace Tedivm\StashBundle\Tests\Adapters;

use Doctrine\Tests\Common\Cache\CacheTest;
use Stash\Driver\Ephemeral;
use Tedivm\StashBundle\Service\CacheService;
use Tedivm\StashBundle\Service\CacheTracker;
use Tedivm\StashBundle\Adapters\DoctrineAdapter;

class DoctrineAdapterTest extends CacheTest
{
    protected $__driver;

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
