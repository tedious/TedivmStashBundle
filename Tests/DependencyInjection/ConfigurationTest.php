<?php

namespace Tedivm\StashBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Tedivm\StashBundle\DependencyInjection\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();

        $configTree = $configuration->getConfigTreeBuilder();

        $this->assertInstanceOf('Symfony\Component\Config\Definition\Builder\TreeBuilder', $configTree);
    }

    public function testAddDriverSettings()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('stash');
        $configuration = new Configuration();
        $configuration->addDriverSettings('Memcache', $rootNode->children());
        $memcacheNode = $rootNode->getNode('Memcache');

        $this->assertInstanceOf('Symfony\Component\Config\Definition\ArrayNode', $memcacheNode,
            'Config generator makes Memcache nodes when requested');

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('stash');
        $configuration = new Configuration();
        $configuration->addDriverSettings('Redis', $rootNode->children());
        $memcacheNode = $rootNode->getNode('Redis');

        $this->assertInstanceOf('Symfony\Component\Config\Definition\ArrayNode', $memcacheNode,
            'Config generator makes Redis nodes when requested');
    }

}
