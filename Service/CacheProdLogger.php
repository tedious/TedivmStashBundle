<?php

namespace Tedivm\StashBundle\Service;

/**
 * Basic in-memory hit logger
 *
 * Does not log query to avoid eating memory in prod env.
 *
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 */
class CacheProdLogger
{
    protected $name;

    protected $calls = 0;
    protected $hits = 0;

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
        return array();
    }
}
