<?php

namespace Tedivm\StashBundle\Tests\Adapters;

class SessionHandlerAdapterTest extends \Stash\Test\SessionTest
{
    protected $testClass = '\Tedivm\StashBundle\Adapters\SessionHandlerAdapter';
    protected $poolClass = '\Tedivm\StashBundle\Service\CacheService';

    protected function getPool()
    {
        return new $this->poolClass('name');
    }
}
