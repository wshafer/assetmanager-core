<?php

namespace AssetManager\Core\Test\Service;

use AssetManager\Core\Resolver\AggregateResolver;
use AssetManager\Core\Resolver\ResolverInterface;
use AssetManager\Core\Service\AssetCacheManager;
use AssetManager\Core\Service\AssetFilterManager;
use AssetManager\Core\Service\AssetManager;
use AssetManager\Core\Service\AssetManagerServiceFactory;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;

class AssetManagerServiceFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $assetFilterManager = new AssetFilterManager();
        $assetCacheManager = $this->getMockBuilder(AssetCacheManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            AggregateResolver::class,
            $this->createMock(ResolverInterface::class)
        );

        $serviceManager->setService(
            AssetFilterManager::class,
            $assetFilterManager
        );

        $serviceManager->setService(
            AssetCacheManager::class,
            $assetCacheManager
        );

        $serviceManager->setService('config', array(
            'asset_manager' => array(
                'Dummy data',
                'Bacon',
            ),
        ));

        $factory = new AssetManagerServiceFactory();
        $this->assertInstanceOf(AssetManager::class, $factory($serviceManager));
    }
}
