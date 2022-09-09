<?php

/*
 * This file is part of "vt/monolog-fluentd-bundle".
 *
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace VT\MonologFluentdBundle\DependencyInjection;

use Fluent\Logger\FluentLogger;
use Monolog\Logger;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('vt_monolog_fluentd');

        $rootNode
            ->children()
                ->scalarNode('host')
                    ->defaultValue(FluentLogger::DEFAULT_ADDRESS)
                ->end()
                ->scalarNode('port')
                    ->defaultValue(FluentLogger::DEFAULT_LISTEN_PORT)
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
            ->end();

        return $treeBuilder;
    }
}
