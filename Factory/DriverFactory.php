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

namespace Tedivm\StashBundle\Factory;
use Stash\DriverList;
use Stash\Interfaces\DriverInterface;

/**
 * Class DriverFactory
 * @package Tedivm\StashBundle\Factory
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class DriverFactory
{
    /**
     * Given a list of cache types and options, creates a CompositeDrivers wrapping the specified drivers.
     *
     * @param $types
     * @param $options
     * @throws \RuntimeException
     * @return DriverInterface
     */
    public static function createDriver($types, $options)
    {
        $drivers = DriverList::getAvailableDrivers();

        $h = array();

        foreach ($types as $type) {

            if (!isset($drivers[$type])) {
                $allDrivers = DriverList::getAllDrivers();

                if (isset($allDrivers[$type])) {
                    throw new \RuntimeException('Driver currently unavailable.');
                } else {
                    throw new \RuntimeException('Driver does not exist.');
                }
            }

            $class = $drivers[$type];

            if ($type === 'Memcache' && isset($options[$type])) {
                // Fix servers spec since underlying drivers expect plain arrays, not hashes.
                $servers = array();
                foreach ($options[$type]['servers'] as $serverSpec) {
                    $servers[] = array(
                        $serverSpec['server'],
                        $serverSpec['port'],
                        isset($serverSpec['weight']) ? $serverSpec['weight'] : null
                    );
                }

                $options[$type]['servers'] = $servers;
            }

            $opts = isset($options[$type]) ? $options[$type] : array();
            $driver = new $class();
            $driver->setOptions($opts);
            $h[] = $driver;
        }

        if (count($h) == 1) {
            return reset($h);
        }

        $class = $drivers['Composite'];
        $driver = new $class();
        $driver->setOptions(array('drivers' => $h));

        return $driver;
    }
}
