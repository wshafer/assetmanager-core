<?php

namespace AssetManager\Core\Service;

use AssetManager\Core\Resolver\PrivateCollectionResolver;
use Psr\Container\ContainerInterface;

class PrivateCollectionResolverServiceFactory
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container)
    {
        $config      = $container->get('config');
        $collections = array();

        if (isset($config['asset_manager']['resolver_configs']['private_collections'])) {
            $collections = $config['asset_manager']['resolver_configs']['private_collections'];
        }

        $privateCollectionResolver = new PrivateCollectionResolver($collections);

        return $privateCollectionResolver;
    }
}
