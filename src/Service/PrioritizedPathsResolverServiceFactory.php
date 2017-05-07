<?php

namespace AssetManager\Core\Service;

use AssetManager\Core\Resolver\PrioritizedPathsResolver;
use Psr\Container\ContainerInterface;

class PrioritizedPathsResolverServiceFactory
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container)
    {
        $config                   = $container->get('config');
        $prioritizedPathsResolver = new PrioritizedPathsResolver();
        $paths                    = isset($config['asset_manager']['resolver_configs']['prioritized_paths'])
            ? $config['asset_manager']['resolver_configs']['prioritized_paths']
            : array();
        $prioritizedPathsResolver->addPaths($paths);

        return $prioritizedPathsResolver;
    }
}
