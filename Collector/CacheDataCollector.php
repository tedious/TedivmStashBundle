<?php

namespace Tedivm\StashBundle\Collector;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Stash;
use StashHandlers;


/**
 * Collects data stored in the static variables of the Stash class for use in profiling/debugging. Currently
 * records total cache calls and returns, along with calls and returns on each individual cache node.
 *
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 */
class CacheDataCollector extends DataCollector
{

	protected $handlerType;
	protected $handlerOptions;

	public function __construct($type, $options)
	{
		$this->handlerType = $type;
		$this->handlerOptions = $options;
	}

	public function collect(Request $request, Response $response, \Exception $exception = null)
	{
		$record = Stash::$queryRecord;
		if(!isset($record))
			$record = array();

		$data = array();
		foreach($record as $query => $calls)
			$data[$query] = array('calls' => count($calls), 'returns' => array_sum($calls));

		$info = array('calls' => Stash::$cacheCalls, 'returns' => Stash::$cacheReturns, 'record' => $data);
		$info['handlerType'] = $this->handlerType;
		$info['handlerOptions'] = $this->handlerOptions;
		$handlers = StashHandlers::getHandlers();
		foreach($handlers as $handler)
			$info['availableHandlers'][] = substr($handler, 5);
		$info['availableHandlers'] = join(', ', $info['availableHandlers']);

		if($this->handlerType === 'MultiHandler') {
			$handlers = $info['handlerOptions']['handlers'];
			$info['handlerOptions']['handlers'] = join(', ', $handlers);

			foreach($handlers as $h) {
				$info['subhandlerOptions'][$h] = isset($info['handlerOptions'][$h]) ? $info['handlerOptions'][$h] : array();
				unset($info['handlerOptions'][$h]);
			}
		}

		$this->data = $info;
	}

	public function getCalls()
	{
		return $this->data['calls'];
	}

	public function getReturns()
	{
		return $this->data['returns'];
	}

	public function getRecord()
	{
		return $this->data['record'];
	}

	public function getHandlertype()
	{
		return $this->data['handlerType'];
	}

	public function getHandleroptions()
	{
		return $this->data['handlerOptions'];
	}

	public function getSubhandleroptions()
	{
		return $this->data['subhandlerOptions'];
	}

	public function gethandlers()
	{
		return $this->data['availableHandlers'];
	}

	public function getname()
	{
		return 'stash';
	}
}
