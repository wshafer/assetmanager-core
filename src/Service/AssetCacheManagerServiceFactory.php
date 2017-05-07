<?php

namespace AssetManager\Core\Service;

use Psr\Container\ContainerInterface;

/**
 * Factory for the Asset Cache Manager Service
 */
class AssetCacheManagerServiceFactory
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = array();

        $globalConfig = $container->get('config');

        if (!empty($globalConfig['asset_manager']['caching'])) {
            $config = $globalConfig['asset_manager']['caching'];
        }

        return new AssetCacheManager($container, $config);
    }
}
