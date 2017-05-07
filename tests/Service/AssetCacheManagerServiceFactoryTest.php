<?php

namespace AssetManager\Core\Test\Service;

use AssetManager\Core\Service\AssetCacheManager;
use AssetManager\Core\Service\AssetCacheManagerServiceFactory;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;

class AssetCacheManagerServiceFactoryTest extends TestCase
{
    public function testConstruct()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            array(
                'asset_manager' => array(
                    'caching' => array(
                        'default' => array(
                            'cache' => 'Apc',
                        ),
                    ),
                ),
            )
        );

        $assetManager = new AssetCacheManagerServiceFactory($serviceManager);
        $service = $assetManager($serviceManager);

        $this->assertTrue($service instanceof AssetCacheManager);
    }
}
