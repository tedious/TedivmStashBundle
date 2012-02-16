<?php

namespace Tedivm\StashBundle\Tests\DependencyInjection;

use Tedivm\StashBundle\DependencyInjection\TedivmStashExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class StashExtensionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider handlerProvider
	 */
	public function testLoadHandlerConfiguration($config, $tests)
	{
		$container = new ContainerBuilder();
		$extension = new TedivmStashExtension();

		$extension->load(array($config), $container);

		$this->assertEquals($config['handler'], $container->getParameter('stash.handler.type'));

		$options = $container->getParameter('stash.handler.options');

		foreach($tests as $option => $value) {
			$this->assertEquals($value, $options[$option]);
		}
	}

	public function handlerProvider()
	{
		return array(
			array(
				'config'	=>	array('handler' => 'FileSystem'),
				'tests'		=>	array('dirSplit' => 2, 'filePermissions' => 0660, 'memKeyLimit' => 200),
			),
			array(
				'config'	=>	array('handler' => 'FileSystem', 'FileSystem' => array('memKeyLimit' => 400)),
				'tests'		=>	array('dirSplit' => 2, 'filePermissions' => 0660, 'memKeyLimit' => 400),
			),
			array(
				'config'	=>	array('handler' => 'SQLite'),
				'tests'		=>	array('nesting' => 0, 'filePermissions' => 0660, 'busyTimeout' => 500),
			),
			array(
				'config'	=>	array('handler' => 'SQLite', 'SQLite' => array('busyTimeout' => 0, 'nesting' => 2)),
				'tests'		=>	array('nesting' => 2, 'filePermissions' => 0660, 'busyTimeout' => 0),
			),
			array(
				'config'	=>	array('handler' => 'MultiHandler'),
				'tests'		=>	array('handlers' => array('FileSystem')),
			),
			array(
				'config'	=>	array('handler' => 'MultiHandler', 'MultiHandler' => array('handlers' => array('SQLite', 'FileSystem'))),
				'tests'		=>	array('handlers' => array('SQLite', 'FileSystem')),
			),
		);
	}
}