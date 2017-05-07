<?php

namespace AssetManager\Core\Test\Config;

use AssetManager\Core\Service\MimeResolver;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManager;

/**
 * Test to ensure config file is properly setup and all services are retrievable
 *
 * @package AssetManagerTest\Config
 */
class ContainerTest extends TestCase
{
    /**
     * Test the Service Managers Factories.
     *
     * @coversNothing
     */
    public function testServiceManagerFactories()
    {
        $config = include __DIR__ . '/../../config/module.config.php';

        $serviceManagerConfig = new Config($config['dependencies']);
        $serviceManager = new ServiceManager();
        $serviceManagerConfig->configureServiceManager($serviceManager);
        $serviceManager->setService('config', $config);

        foreach ($config['dependencies']['factories'] as $serviceName => $service) {
            $this->assertTrue($serviceManager->has($serviceName));

            //Make sure we can fetch the service
            $service = $serviceManager->get($serviceName);

            $this->assertTrue(is_object($service));
        }
    }

    /**
     * Test the Service Managers Invokables.
     *
     * @coversNothing
     */
    public function testServiceManagerInvokables()
    {
        $config = include __DIR__ . '/../../config/module.config.php';

        $serviceManagerConfig = new Config($config['dependencies']);
        $serviceManager = new ServiceManager();
        $serviceManagerConfig->configureServiceManager($serviceManager);
        $serviceManager->setService('config', $config);

        foreach ($config['dependencies']['invokables'] as $serviceName => $service) {
            $this->assertTrue($serviceManager->has($serviceName));

            //Make sure we can fetch the service
            $service = $serviceManager->get($serviceName);

            $this->assertTrue(is_object($service));
        }
    }

    /**
     * Test for Issue #134 - Test for specific mime_resolver invokable
     *
     * @coversNothing
     */
    public function testMimeResolverInvokable()
    {
        $config = include __DIR__ . '/../../config/module.config.php';

        $serviceManagerConfig = new Config($config['dependencies']);
        $serviceManager = new ServiceManager();
        $serviceManagerConfig->configureServiceManager($serviceManager);
        $serviceManager->setService('config', $config);

        $this->assertTrue($serviceManager->has(MimeResolver::class));
        $this->assertTrue(is_object($serviceManager->get(MimeResolver::class)));
    }
}
