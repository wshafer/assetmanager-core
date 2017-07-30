<?php

namespace AssetManager\Core\Test\Service;

use AssetManager\Core\Resolver\CollectionResolver;
use AssetManager\Core\Resolver\PrivateCollectionResolver;
use AssetManager\Core\Service\PrivateCollectionResolverServiceFactory;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;

class PrivateCollectionResolverServiceFactoryTest extends TestCase
{
    /**
     * Mainly to avoid regressions
     */
    public function testCreateService()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            array(
                'asset_manager' => array(
                    'resolver_configs' => array(
                        'private_collections' => array(
                            'path1.jpg',
                            'path2.jpg',
                        ),
                    ),
                ),
            )
        );

        $factory = new PrivateCollectionResolverServiceFactory();
        /* @var PrivateCollectionResolver */
        $collectionsResolver = $factory($serviceManager);
        $this->assertSame(
            array(
                'path1.jpg',
                'path2.jpg',
            ),
            $collectionsResolver->getCollections()
        );
    }

    /**
     * Mainly to avoid regressions
     */
    public function testCreateServiceWithNoConfig()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', array());

        $factory = new PrivateCollectionResolverServiceFactory();
        /* @var CollectionResolver */
        $collectionsResolver = $factory($serviceManager);
        $this->assertEmpty($collectionsResolver->getCollections());
    }
}
