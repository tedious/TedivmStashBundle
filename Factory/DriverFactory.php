<?php

namespace Tedivm\StashBundle\Factory;
use Stash\Drivers,
    Stash\Interfaces\DriverInterface;

class DriverFactory
{
    /**
     * Given a list of cache types and options, creates a CompositeDrivers wrapping the specified drivers.
     *
     * @param $types
     * @param $options
     * @return DriverInterface
     */
    public static function createDriver($types, $options)
    {
        $drivers = Drivers::getDrivers();

        $h = array();

        foreach ($types as $type) {
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
            $h[] = new $class($opts);
        }

        if (count($h) == 1) {
            return reset($h);
        }

        $class = $drivers['Composite'];
        $driver = new $class(array('drivers' => $h));

        return $driver;
    }
}
