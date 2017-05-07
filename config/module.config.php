<?php
return [
    'dependencies' => [
        'factories'  => [
            AssetManager\Core\Service\AssetManager::class              => AssetManager\Core\Service\AssetManagerServiceFactory::class,
            AssetManager\Core\Service\AssetFilterManager::class        => AssetManager\Core\Service\AssetFilterManagerServiceFactory::class,
            AssetManager\Core\Service\AssetCacheManager::class         => AssetManager\Core\Service\AssetCacheManagerServiceFactory::class,
            AssetManager\Core\Resolver\AggregateResolver::class        => AssetManager\Core\Service\AggregateResolverServiceFactory::class,
            AssetManager\Core\Resolver\MapResolver::class              => AssetManager\Core\Service\MapResolverServiceFactory::class,
            AssetManager\Core\Resolver\PathStackResolver::class        => AssetManager\Core\Service\PathStackResolverServiceFactory::class,
            AssetManager\Core\Resolver\PrioritizedPathsResolver::class => AssetManager\Core\Service\PrioritizedPathsResolverServiceFactory::class,
            AssetManager\Core\Resolver\CollectionResolver::class       => AssetManager\Core\Service\CollectionResolverServiceFactory::class,
            AssetManager\Core\Resolver\ConcatResolver::class           => AssetManager\Core\Service\ConcatResolverServiceFactory::class,
            AssetManager\Core\Resolver\AliasPathStackResolver::class   => AssetManager\Core\Service\AliasPathStackResolverServiceFactory::class,
        ],
        'invokables' => [
            AssetManager\Core\Service\MimeResolver::class => AssetManager\Core\Service\MimeResolver::class,
        ],
    ],
    'asset_manager'   => [
        'clear_output_buffer' => true,
        'resolvers'           => [
            AssetManager\Core\Resolver\MapResolver::class              => 3000,
            AssetManager\Core\Resolver\ConcatResolver::class           => 2500,
            AssetManager\Core\Resolver\CollectionResolver::class       => 2000,
            AssetManager\Core\Resolver\PrioritizedPathsResolver::class => 1500,
            AssetManager\Core\Resolver\AliasPathStackResolver::class   => 1000,
            AssetManager\Core\Resolver\PathStackResolver::class        => 500,
        ],
        'view_helper'         => [
            'append_timestamp' => true,
            'query_string'     => '_',
            'cache'            => null,
        ],
    ],
];
