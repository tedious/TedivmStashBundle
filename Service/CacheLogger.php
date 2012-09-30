<?php

namespace Tedivm\StashBundle\Service;

/**
 * Basic in-memory query
 *
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 */
class CacheLogger
{
    protected $name;

    protected $calls = 0;
    protected $hits = 0;

    protected $queries = array();

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function logRequest($key, $hit, $value)
    {
        $this->calls++;
        if($hit) {
            $this->hits++;
        }
        $this->queries[] = array(
            'key'   => $key,
            'hit'   => $hit,
            'value' => $value
        );
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCalls()
    {
        return $this->calls;
    }

    public function getHits()
    {
        return $this->hits;
    }

    public function getQueries()
    {
        return $this->queries;
    }
}