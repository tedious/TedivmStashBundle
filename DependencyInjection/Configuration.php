<?php

namespace Tedivm\StashBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use StashHandlers;

class Configuration implements ConfigurationInterface
{

	protected $handlerSettings = array(
		'FileSystem' => array(
			'dirSplit' 			=> 2,
			'path' 				=> '%kernel.cache_dir%/stash',
			'filePermissions' 	=> 0660,
			'dirPermissions' 	=> 0770,
			'memKeyLimit' 		=> 200
		),
		'SQLite' => array(
			'filePermissions'	=> 0660,
			'dirPermissions'	=> 0770,
			'busyTimeout'		=> 500,
			'nesting'			=> 0,
			'subhandler'		=> 'PDO',
			'version'			=> null,
			'path'				=> '%kernel.cache_dir%/stash',
		),
		'APC' => array(
			'ttl'				=> 300,
			'namespace'			=> null,
		),
	);

	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('tedivm_stash');

		$handlers = array_keys(StashHandlers::getHandlers());
		$default = current($handlers);

		$next = $rootNode
			->children()
				->scalarNode('handler')
					->defaultValue($default)
					->validate()
					->ifNotInArray($handlers)
						->thenInvalid('No Stash handler named %s registered.')
					->end()
				->end()
			->end()
		;

		foreach($handlers as $handler) {
			$this->addHandlerSettings($handler, $rootNode);
		}

		return $treeBuilder;
	}

	public function addHandlerSettings($handler, $rootNode)
	{
		if($handler == 'Memcached') {
			$rootNode
				->children()
					->variableNode($handler)
						->defaultValue(array())
					->end()
				->end()
			;
			return;
		} elseif ($handler == 'MultiHandler') {
		    $this->addMultiHandlerSettings($rootNode);
		    return;
		} else {
			$node = $rootNode->children()->arrayNode($handler)->addDefaultsIfNotSet()->children();

			if(!isset($this->handlerSettings[$handler])) {
				$node->end()->end()->end();
				return;
			}

			foreach($this->handlerSettings[$handler] as $setting => $default) {
				$set = $node->scalarNode($setting);
				if(isset($default))
					$set = $set->defaultValue($default);
				$end = $set->end();
			}

			$end->end()->end();
		}
	}

	public function addMultiHandlerSettings($rootNode)
	{
		$node = $rootNode
			->children()
				->arrayNode('MultiHandler')
				->addDefaultsIfNotSet()
					->children();

					$node
						->VariableNode('handlers')
							->defaultValue(array('FileSystem'))
						->end()
					;

					$node = $node
				->end();

				$handlers = array_keys(StashHandlers::getHandlers());
				foreach($handlers as $handler) {
					if($handler !== 'MultiHandler') {
						$this->addHandlerSettings($handler, $node);
					}
				}

				$node->end()
			->end()
		;

	}
}
