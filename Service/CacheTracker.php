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

namespace Tedivm\StashBundle\Service;

/**
 * Class CacheTracker
 * @package Tedivm\StashBundle\Service
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class CacheTracker
{
    /**
     * The name of the cache being logged.
     *
     * @var
     */
    protected $name;

    /**
     * The total number of calls to the cache service.
     *
     * @var int
     */
    protected $calls = 0;

    /**
     * The number of calls that returned as hits.
     *
     * @var int
     */
    protected $hits = 0;

    /**
     * The record of queries against the cache.
     *
     * @var array
     */
    protected $queries = array();

    /**
     * Whether to log individual queries or only hits/misses.
     *
     * @var bool
     */
    protected $logQueries = true;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Enables or disables query logging.
     *
     * @param boolean $lq
     */
    public function enableQueryLogging($lq = true)
    {
        $this->logQueries = $lq;
    }

    /**
     * Log a request against the cache.
     *
     * @param $key
     * @param $hit
     * @param $value
     */
    public function trackRequest($key, $hit, $value)
    {
        $this->calls++;
        if ($hit) {
            $this->hits++;
        }

        if (!$this->logQueries) {
            return;
        }

        $hit = $hit ? 'true' : 'false';
        $value = sprintf('(%s) %s', gettype($value), print_r($value, true));

        $this->queries[] = array(
            'key'   => $key,
            'hit'   => $hit,
            'value' => $value
        );
    }

    /**
     * Get the name of the cache.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the number of calls.
     *
     * @return int
     */
    public function getCalls()
    {
        return $this->calls;
    }

    /**
     * Get the number of hits.
     *
     * @return int
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * Get the record of queries.
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->queries;
    }
}
