<?php

namespace AssetManager\Core\Service;

use Psr\Container\ContainerInterface;

class AssetFilterManagerServiceFactory
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container)
    {
        $filters = array();
        $config  = $container->get('config');

        if (!empty($config['asset_manager']['filters'])) {
            $filters = $config['asset_manager']['filters'];
        }

        $assetFilterManager = new AssetFilterManager($filters);

        $assetFilterManager->setContainer($container);

        return $assetFilterManager;
    }
}
