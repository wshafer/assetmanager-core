<?php

namespace AssetManager\Core\Service;

use AssetManager\Core\Resolver\PathStackResolver;
use Psr\Container\ContainerInterface;

class PathStackResolverServiceFactory
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container)
    {
        $config            = $container->get('config');
        $pathStackResolver = new PathStackResolver();
        $paths             = array();

        if (isset($config['asset_manager']['resolver_configs']['paths'])) {
            $paths = $config['asset_manager']['resolver_configs']['paths'];
        }

        $pathStackResolver->addPaths($paths);

        return $pathStackResolver;
    }
}
