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

namespace Tedivm\StashBundle\Tests;

/**
 * Class TedivmStashBundleTest
 * @package Tedivm\StashBundle\Tests
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class TedivmStashBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testGetContainerExtension()
    {
        $bundle = new \Tedivm\StashBundle\TedivmStashBundle();

        $this->assertInstanceOf('Tedivm\StashBundle\DependencyInjection\TedivmStashExtension',
            $bundle->getContainerExtension());
    }
}
