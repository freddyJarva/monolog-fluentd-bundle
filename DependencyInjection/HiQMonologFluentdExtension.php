<?php

namespace HiQ\MonologFluentdBundle\DependencyInjection;

use Monolog\Logger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class HiQMonologFluentdExtension extends Extension
{

    /**
     * @inheritDoc
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Converts PSR-3 levels to Monolog ones if necessary
        $config['level'] = Logger::toMonologLevel($config['level']);

        $container->setParameter('monolog_fluentd.host', $config['host']);
        $container->setParameter('monolog_fluentd.port', $config['port']);
        $container->setParameter('monolog_fluentd.options', $config['options']);
        $container->setParameter('monolog_fluentd.level', $config['level']);
        $container->setParameter('monolog_fluentd.tag_fmt', $config['tag_fmt']);
        $container->setParameter('monolog_fluentd.enable_exceptions', $config['enable_exceptions']);
        $container->setParameter('monolog_fluentd.channels', $config['channels']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }

    /**
     * @inheritDoc
     */
    public function getAlias(): string
    {
        return "hiq_monolog_fluentd";
    }

}