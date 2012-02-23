<?php

namespace Tedivm\StashBundle\Service;
use Stash\Cache;
use Stash\Handlers;

/**
 * Simple result-object provider for the Stash class.
 *
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 */
class Cache
{
	protected $handler;

	/**
	 * Constructs the cache holder. Parameter is a Stash handler which is dynamically injected at service creation.
	 *
	 * @param StashHandler $handler
	 */
	public function __construct($handler)
	{
		$this->handler = $handler;
	}

	/**
	 * Returns a Stash caching object for the specified key. The key can be either a series of string arguments,
	 * or an array.
	 *
	 * @param array|string $key, $key, $key...
	 */
	public function get()
	{
		$args = func_get_args();

		// check to see if a single array was used instead of multiple arguments
		if(count($args) == 1 && is_array($args[0]))
			$args = $args[0];

		$handler = (isset($this->handler)) ? $this->handler : null;
		$stash = new Cache($handler);

		if(count($args) > 0)
			$stash->setupKey($args);

		return $stash;
	}

	/**
	 * Clears the cache for the key, or if none is specified clears the entire cache. The key can be either
	 * a series of string arguments, or an array.
	 *
	 * @param null|string|array $key, $key, $key...
	 */
	public function clear()
	{
		$stash = $this->getCache(func_get_args());
		return $stash->clear();
	}

	/**
	 * Purges the cache of all stale or obsolete objects, as well as other maintenance tasks specified by the
	 * back end caching system. This operation has the potential to be very long running.
	 *
	 * @return bool
	 */
	public function purge()
	{
		$stash = $this->getCache();
		return $stash->purge();
	}

	/**
	 * Returns the current list of handlers that the system is able to use.
	 *
	 * @return array
	 */
	public function getHandlers()
	{
		return Handlers::getHandlers();
	}
}
