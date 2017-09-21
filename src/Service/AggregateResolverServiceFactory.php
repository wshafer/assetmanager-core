<?php

namespace AssetManager\Core\Service;

use AssetManager\Core\Exception;
use AssetManager\Core\Resolver\AggregateResolver;
use AssetManager\Core\Resolver\AggregateResolverAwareInterface;
use AssetManager\Core\Resolver\MimeResolverAwareInterface;
use AssetManager\Core\Resolver\ResolverInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory class for AssetManagerService
 *
 * @category   AssetManager
 * @package    AssetManager
 */
class AggregateResolverServiceFactory
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container)
    {
        $config         = $container->get('config');
        $config         = isset($config['asset_manager']) ? $config['asset_manager'] : array();
        $resolver       = new AggregateResolver();

        if (empty($config['resolvers'])) {
            return $resolver;
        }

        foreach ($config['resolvers'] as $resolverService => $priority) {
            $resolverService = $container->get($resolverService);

            if (!$resolverService instanceof ResolverInterface) {
                throw new Exception\RuntimeException(
                    'Service does not implement the required interface ResolverInterface.'
                );
            }

            if ($resolverService instanceof AggregateResolverAwareInterface) {
                $resolverService->setAggregateResolver($resolver);
            }

            if ($resolverService instanceof MimeResolverAwareInterface) {
                $resolverService->setMimeResolver($container->get(MimeResolver::class));
            }

            if ($resolverService instanceof AssetFilterManagerAwareInterface) {
                $resolverService->setAssetFilterManager(
                    $container->get(AssetFilterManager::class)
                );
            }

            $resolver->attach($resolverService, $priority);
        }

        return $resolver;
    }
}
