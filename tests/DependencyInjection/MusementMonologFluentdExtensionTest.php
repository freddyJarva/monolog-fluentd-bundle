<?php

/*
 * This file is part of "vt/monolog-fluentd-bundle".
 *
 * (c) VT S.p.A. <oss@vt.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace VT\MonologFluentdBundle\Tests\DependencyInjection;

use Monolog\Logger;
use VT\MonologFluentdBundle\DependencyInjection\VTMonologFluentdExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class VTMonologFluentdExtensionTest extends TestCase
{
    /** @var VTMonologFluentdExtension */
    protected $loader;

    /** @var ContainerBuilder */
    protected $container;

    protected function setUp(): void
    {
        $this->loader = new VTMonologFluentdExtension();
        $this->container = new ContainerBuilder();
    }

    public function testParameterHost()
    {
        $this->loader->load($this->getConfig(), $this->container);
        $this->assertParameter('localhost', 'vt_monolog_fluentd.host');
    }

    public function testParameterPort()
    {
        $this->loader->load($this->getConfig(), $this->container);
        $this->assertParameter(24224, 'vt_monolog_fluentd.port');
    }

    public function testParameterOptions()
    {
        $this->loader->load($this->getConfig(), $this->container);
        $this->assertParameter([], 'vt_monolog_fluentd.options');
    }

    public function testParameterLevelAsInt()
    {
        $config = $this->getConfig();
        $config['vt_monolog_fluentd']['level'] = Logger::DEBUG;
        $this->loader->load($config, $this->container);
        $this->assertParameter(Logger::DEBUG, 'vt_monolog_fluentd.level');
    }

    public function testParameterLevelAsString()
    {
        $config = $this->getConfig();
        $config['vt_monolog_fluentd']['level'] = 'dEbUg';
        $this->loader->load($config, $this->container);
        $this->assertParameter(Logger::DEBUG, 'vt_monolog_fluentd.level');
    }

    public function testParameterTagFmt()
    {
        $config = $this->getConfig();
        $this->loader->load($config, $this->container);
        $this->assertParameter('{{channel}}.{{level_name}}', 'vt_monolog_fluentd.tag_fmt');
    }

    public function testParameterEnableExceptions()
    {
        $config = $this->getConfig();
        $this->loader->load($config, $this->container);
        $this->assertParameter(false, 'vt_monolog_fluentd.enable_exceptions');
    }

    protected function assertParameter($value, $key)
    {
        $this->assertEquals($value, $this->container->getParameter($key));
    }

    protected function getConfig()
    {
        $yaml = <<<'EOF'
vt_monolog_fluentd:
    host: localhost
    port: 24224
    options: []
    level: debug
    tag_fmt: '{{channel}}.{{level_name}}'
    enable_exceptions: false
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }
}
