<?php

namespace Tedivm\StashBundle\Tests\Service;

use Tedivm\StashBundle\Service\Cache;
use Stash\Handler\Ephemeral;

class CacheTest extends \PHPUnit_Framework_TestCase
{
	protected $cache;

	public static function setUpBeforeClass()
	{
		define('TESTING', true);
	}

	protected function setUp()
	{
		$handler = new Ephemeral(array());
		$this->cache = new Cache($handler);
	}

	public function testCache()
	{
		$this->runCacheCycle('one', true);
		$this->runCacheCycle('two', true);

		$this->runCacheCycle('one', false);
		$this->runCacheCycle('two', false);

		$this->cache->clear('test', 'key', 'one');

		$this->runCacheCycle('one', true);
		$this->runCacheCycle('two', false);

		$this->cache->clear();

		$this->runCacheCycle('one', true);
		$this->runCacheCycle('two', true);
	}

	protected function runCacheCycle($num, $ismiss)
	{
		$cache = $this->cache->get('test', 'key', $num);
		$data = $cache->get();
		$this->assertEquals($ismiss, $cache->isMiss());

		$this->assertTrue($cache->store('testkey'.$num));

		$cache = $this->cache->get('test', 'key', $num);
		$data = $cache->get();
		$this->assertFalse($cache->isMiss());
		$this->assertEquals('testkey'.$num, $data);
	}
}