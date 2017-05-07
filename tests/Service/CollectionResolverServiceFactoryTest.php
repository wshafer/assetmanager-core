<?php

namespace AssetManager\Core\Test\Service;

use AssetManager\Core\Resolver\CollectionResolver;
use AssetManager\Core\Service\CollectionResolverServiceFactory;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;

class CollectionResolverServiceFactoryTest extends TestCase
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
                        'collections' => array(
                            'key1' => 'value1',
                            'key2' => 'value2',
                        ),
                    ),
                ),
            )
        );

        $factory = new CollectionResolverServiceFactory();
        /* @var CollectionResolver */
        $collectionsResolver = $factory($serviceManager);
        $this->assertSame(
            array(
                'key1' => 'value1',
                'key2' => 'value2',
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

        $factory = new CollectionResolverServiceFactory();
        /* @var CollectionResolver */
        $collectionsResolver = $factory($serviceManager);
        $this->assertEmpty($collectionsResolver->getCollections());
    }
}
