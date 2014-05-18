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

namespace Tedivm\StashBundle\Collector;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Stash\DriverList;

/**
 * Class CacheDataCollector
 *
 * Collects data stored in the static variables of the Stash class for use in profiling/debugging. Currently
 * records total cache calls and returns, along with calls and returns on each individual cache node.
 *
 * @package Tedivm\StashBundle\Collector
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class CacheDataCollector extends DataCollector
{
    /**
     * The name of the default cache service.
     *
     * @var string
     */
    protected $defaultCache;

    /**
     * The names of the defined cache services.
     *
     * @var array
     */
    protected $cacheNames;

    /**
     * The options for all defined cache services.
     *
     * @var array
     */
    protected $cacheOptions;

    /**
     * The tracker classes for all cache services.
     *
     * @var array
     */
    protected $trackers = array();

    public function __construct($default, $caches, $options)
    {
        $this->defaultCache = $default;
        $this->cacheNames = $caches;
        $this->cacheOptions = $options;
    }

    /**
     * Inject the tracker for a cache service.
     *
     * @param $tracker
     */
    public function addTracker($tracker)
    {
        $this->trackers[] = $tracker;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $info = array('calls' => 0, 'hits' => 0);
        foreach ($this->trackers as $tracker) {
            $name = $tracker->getName();
            $calls = $tracker->getCalls();
            $hits = $tracker->getHits();

            $info['calls'] += $calls;
            $info['hits'] += $hits;

            $info['caches'][$name]['options'] = $this->cacheOptions[$name];
            $info['caches'][$name]['queries'] = $tracker->getQueries();
            $info['caches'][$name]['calls'] = $calls;
            $info['caches'][$name]['hits'] = $hits;
        }

        $drivers = DriverList::getAvailableDrivers();
        foreach ($drivers as $name => $class) {
            if (!in_array($name, array('Ephemeral', 'Composite'))) {
                $info['availableDrivers'][] = $name;
            }
        }

        $info['default'] = $this->defaultCache;

        $this->data = $info;
    }

    /**
     * Returns total cache calls made.
     */
    public function getCalls()
    {
        return $this->data['calls'];
    }

    /**
     * Returns the number of cache calls that were hits.
     */
    public function getHits()
    {
        return $this->data['hits'];
    }

    /**
     * Returns the list of available drivers.
     */
    public function getDrivers()
    {
        return $this->data['availableDrivers'];
    }

    /**
     * Returns the list of cache services.
     */
    public function getCaches()
    {
        return $this->data['caches'];
    }

    /**
     * Returns the name of the default cache service.
     */
    public function getDefault()
    {
        return $this->data['default'];
    }

    /**
     * Returns the name of the data collector.
     */
    public function getname()
    {
        return 'stash';
    }
}
