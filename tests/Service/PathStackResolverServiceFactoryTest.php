<?php

namespace AssetManager\Core\Test\Service;

use AssetManager\Core\Resolver\PathStackResolver;
use AssetManager\Core\Service\PathStackResolverServiceFactory;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;

class PathStackResolverServiceFactoryTest extends TestCase
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
                        'paths' => array(
                            'path1/',
                            'path2/',
                        ),
                    ),
                ),
            )
        );

        $factory = new PathStackResolverServiceFactory();
        /* @var $resolver PathStackResolver */
        $resolver = $factory($serviceManager);
        $this->assertSame(
            array(
                'path2/',
                'path1/',
            ),
            $resolver->getPaths()->toArray()
        );
    }

    /**
     * Mainly to avoid regressions
     */
    public function testCreateServiceWithNoConfig()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('config', array());

        $factory = new PathStackResolverServiceFactory();
        /* @var $resolver PathStackResolver */
        $resolver = $factory($serviceManager);
        $this->assertEmpty($resolver->getPaths()->toArray());
    }
}
