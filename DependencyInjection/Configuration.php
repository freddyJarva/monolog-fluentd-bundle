<?php

namespace HiQ\MonologFluentdBundle\DependencyInjection;

use HiQ\MonologFluentdBundle\Logger\FluentdLogger;
use Monolog\Logger;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 */
class Configuration implements ConfigurationInterface
{

    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder("hiq_monolog_fluentd");
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()->scalarNode('host')->defaultValue(FluentdLogger::DEFAULT_ADDRESS)->end();
        $rootNode->children()
            ->scalarNode('port')
            ->defaultValue(FluentdLogger::DEFAULT_LISTEN_PORT)
            ->end()
            ->variableNode('options')
            ->defaultValue([])
            ->end()
            ->scalarNode('level')
            ->defaultValue(Logger::DEBUG)
            ->end()
            ->scalarNode('tag_fmt')
            ->defaultValue(null)
            ->end()
            ->scalarNode('enable_exceptions')
            ->defaultValue(true)
            ->end()
            ->variableNode('channels')
            ->defaultValue([
                "CHANNEL_STATISTICS" => "statistics",
                "CHANNEL_PLANIT" => "planit",
                "CHANNEL_PHP" => "php",
                "CONTAINER" => "planet",
            ])
            ->end()
            ->end();

        return $treeBuilder;
    }
}