<?php

namespace Tedivm\StashBundle\Collector;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Stash\Cache;
use Stash\Handlers;


/**
 * Collects data stored in the static variables of the Stash class for use in profiling/debugging. Currently
 * records total cache calls and returns, along with calls and returns on each individual cache node.
 *
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 */
class CacheDataCollector extends DataCollector
{
    protected $defaultCache;
    protected $cacheNames;
    protected $cacheOptions;

    protected $loggers = array();

    public function __construct($default, $caches, $options)
    {
        $this->defaultCache = $default;
        $this->cacheNames = $caches;
        $this->cacheOptions = $options;
    }

    public function addLogger($logger)
    {
        $this->loggers[] = $logger;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $info = array('calls' => 0, 'hits' => 0);
        foreach($this->loggers as $logger) {
            $name = $logger->getName();
            $calls = $logger->getCalls();
            $hits = $logger->getHits();

            $info['calls'] += $calls;
            $info['hits'] += $hits;

            $info['caches'][$name]['options'] = $this->cacheOptions[$name];
            $info['caches'][$name]['queries'] = $logger->getQueries();
            $info['caches'][$name]['calls'] = $calls;
            $info['caches'][$name]['hits'] = $hits;
        }

        $handlers = Handlers::getHandlers();
        foreach($handlers as $handler) {
            $pieces = explode('\\', $handler);
            $name = array_pop($pieces);
            if(!in_array($name, array('Ephemeral', 'MultiHandler'))) {
                $info['availableHandlers'][] = $name;
            }
        }

        $info['default'] = $this->defaultCache;

        $this->data = $info;
    }

    public function getCalls()
    {
        return $this->data['calls'];
    }

    public function getHits()
    {
        return $this->data['hits'];
    }

    public function gethandlers()
    {
        return $this->data['availableHandlers'];
    }

    public function getCaches()
    {
        return $this->data['caches'];
    }

    public function getDefault()
    {
        return $this->data['default'];
    }

    public function getname()
    {
        return 'stash';
    }
}
