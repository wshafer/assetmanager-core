<?php

namespace AssetManager\Core\Test\Service;

use AssetManager\Core\Service\AssetFilterManager;
use AssetManager\Core\Service\AssetFilterManagerServiceFactory;
use AssetManager\Core\Service\MimeResolver;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\ServiceManager;

class AssetFilterManagerServiceFactoryTest extends TestCase
{
    public function testConstruct()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(
            'config',
            array(
                'asset_manager' => array(
                    'filters' => array(
                        'css' => array(
                            'filter' => 'Lessphp',
                        ),
                    ),
                ),
            )
        );

        $serviceManager->setService(MimeResolver::class, new MimeResolver);

        $t = new AssetFilterManagerServiceFactory($serviceManager);
        $service = $t($serviceManager);

        $this->assertTrue($service instanceof AssetFilterManager);
    }
}
