<?php

namespace Tedivm\StashBundle\Service;
use Stash;
use StashHandlers;

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
	 * @var StashHandler $handler
	 */
	public function __construct($handler)
	{
		$this->handler = $handler;
	}

	/**
	 * Returns a Stash caching object for the specified key.
	 *
	 * @param string $key, $key, $key...
	 */
	public function get()
	{
		$args = func_get_args();

		// check to see if a single array was used instead of multiple arguments
		if(count($args) == 1 && is_array($args[0]))
			$args = $args[0];

		$handler = (isset($this->handler)) ? $this->handler : null;
		$stash = new Stash($handler, 'stashbox');

		if(count($args) > 0)
			$stash->setupKey($args);

		return $stash;
	}

	/**
	 * Clears the cache (for the key, if specified.)
	 *
	 * @param null|string|array $key, $key, $key...
	 */
	public function clear()
	{
		$stash = $this->getCache(func_get_args());
		return $stash->clear();
	}

	/**
	 * Purges the cache.
	 *
	 */
	public function purge()
	{
		$stash = $this->getCache();
		return $stash->purge();
	}

	/**
	 * Returns the current list of handlers.
	 *
	 * @return array
	 */
	public function getHandlers()
	{
		return StashHandlers::getHandlers();
	}
}
