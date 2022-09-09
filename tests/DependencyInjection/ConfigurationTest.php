<?php

/*
 * This file is part of "vt/monolog-fluentd-bundle".
 *
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace VT\MonologFluentdBundle\Tests\DependencyInjection;

use VT\MonologFluentdBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class ConfigurationTest extends TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();
        $treeBuilder = $configuration->getConfigTreeBuilder();
        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
    }
}
