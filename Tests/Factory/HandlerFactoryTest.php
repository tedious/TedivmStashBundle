<?php

namespace Tedivm\StashBundle\Tests\Factory;

use Tedivm\StashBundle\Factory\HandlerFactory;

class HandlerFactoryExtensionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider handlerProvider
	 */
	public function testManufactureHandlers($class, $type, $options, $tests)
	{
		$handler = HandlerFactory::createHandler($type, $options);

		if(isset($tests['path']) && $tests['path'] === 'GETPATH')
			$tests['path'] = \StashUtilities::getBaseDirectory($handler);

		$this->assertInstanceOf($class, $handler);
		foreach($tests as $attribute => $value) {
			$this->assertAttributeEquals($value, $attribute, $handler);
		}
	}

	/**
	 * @dataProvider multiHandlerProvider
	 */
	public function testMultiHandler($options, $classes, $tests)
	{
		$handler = HandlerFactory::createHandler('MultiHandler', $options);

		$handlers = \PHPUnit_Util_Class::getObjectAttribute($handler, 'handlers');

		foreach($handlers as $subhandler) {
			$class = array_shift($classes);
			$this->assertInstanceOf($class, $subhandler);

			$ts = array_shift($tests);
			foreach($ts as $attribute => $value) {
				$this->assertAttributeEquals($value, $attribute, $subhandler);
			}
		}
	}

	public function handlerProvider()
	{
		return array(
			array(
				'class'		=> 'StashFileSystem',
				'type'		=> 'FileSystem',
				'options'	=> array(),
				'tests'		=> array('directorySplit' => 2, 'memStoreLimit' => 20),
			),
			array(
				'class'		=> 'StashFileSystem',
				'type'		=> 'FileSystem',
				'options'	=> array('dirSplit' => 3, 'memKeyLimit' => 21),
				'tests'		=> array('directorySplit' => 3, 'memStoreLimit' => 21),
			),
			array(
				'class'		=> 'StashSqlite',
				'type'		=> 'SQLite',
				'options'	=> array(),
				'tests'		=> array('nesting' => 0, 'handlerClass' => 'StashSqlite_PDO', 'path' => 'GETPATH'),
			),
			array(
				'class'		=> 'StashSqlite',
				'type'		=> 'SQLite',
				'options'	=> array('nesting' => 2, 'extension' => 'sqlite'),
				'tests'		=> array('nesting' => 2, 'handlerClass' => 'StashSqlite_SQLite'),
			),
		);
	}

	public function multiHandlerProvider()
	{
		return array(
			array(
				'options'	=> array('handlers' => array('FileSystem')),
				'classes'	=> array('StashFileSystem'),
				'tests'		=> array(
					array('directorySplit' => 2, 'memStoreLimit' => 20),
				),
			),
			array(
				'options'	=> array('handlers' => array('FileSystem', 'SQLite')),
				'classes'	=> array('StashFileSystem', 'StashSqlite'),
				'tests'		=> array(
					array('directorySplit' => 2, 'memStoreLimit' => 20),
					array('nesting' => 0, 'handlerClass' => 'StashSqlite_PDO'),
				),
			),
		);
	}
}