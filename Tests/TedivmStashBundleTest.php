<?php

namespace Tedivm\StashBundle\Tests;

class TedivmStashBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testGetContainerExtension()
    {
        $bundle = new \Tedivm\StashBundle\TedivmStashBundle();

        $this->assertInstanceOf('Tedivm\StashBundle\DependencyInjection\TedivmStashExtension',
            $bundle->getContainerExtension());
    }
}
