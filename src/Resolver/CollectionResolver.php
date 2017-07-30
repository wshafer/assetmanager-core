<?php

namespace AssetManager\Core\Resolver;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetInterface;
use AssetManager\Core\Exception;
use AssetManager\Core\Service\AssetFilterManager;
use AssetManager\Core\Service\AssetFilterManagerAwareInterface;
use Traversable;

/**
 * This resolver allows the resolving of collections.
 * Collections are strictly checked by mime-type,
 * and added to an AssetCollection when all checks passed.
 */
class CollectionResolver implements
    ResolverInterface,
    AggregateResolverAwareInterface,
    AssetFilterManagerAwareInterface
{
    use CollectionTrait;

    /**
     * @var ResolverInterface
     */
    protected $aggregateResolver;

    /**
     * @var AssetFilterManager The filterManager service.
     */
    protected $filterManager;

    /**
     * Constructor
     *
     * Instantiate and optionally populate collections.
     *
     * @param array|Traversable $collections
     */
    public function __construct($collections = array())
    {
        $this->setCollections($collections);
    }

    /**
     * Set the aggregate resolver.
     *
     * @param ResolverInterface $aggregateResolver
     */
    public function setAggregateResolver(ResolverInterface $aggregateResolver)
    {
        $this->aggregateResolver = $aggregateResolver;
    }

    /**
     * Get the aggregate resolver.
     *
     * @return ResolverInterface
     */
    public function getAggregateResolver()
    {
        return $this->aggregateResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name)
    {
        $collections = $this->getCollections();

        if (!isset($collections[$name])) {
            return null;
        }

        if (!is_array($collections[$name])) {
            throw new Exception\RuntimeException(
                "Collection with name $name is not an an array."
            );
        }

        $collection = new AssetCollection;
        $mimeType   = null;
        $collection->setTargetPath($name);

        foreach ($collections[$name] as $asset) {
            if (!is_string($asset)) {
                throw new Exception\RuntimeException(
                    'Asset should be of type string. got ' . gettype($asset)
                );
            }

            if (null === ($res = $this->getAggregateResolver()->resolve($asset))) {
                throw new Exception\RuntimeException("Asset '$asset' could not be found.");
            }

            if (!$res instanceof AssetInterface) {
                throw new Exception\RuntimeException(
                    "Asset '$asset' does not implement Assetic\\Asset\\AssetInterface."
                );
            }

            if (null !== $mimeType && $res->mimetype !== $mimeType) {
                throw new Exception\RuntimeException(sprintf(
                    'Asset "%s" from collection "%s" doesn\'t have the expected mime-type "%s".',
                    $asset,
                    $name,
                    $mimeType
                ));
            }

            $mimeType = $res->mimetype;

            $this->getAssetFilterManager()->setFilters($asset, $res);

            $collection->add($res);
        }

        $collection->mimetype = $mimeType;

        return $collection;
    }

    /**
     * Set the AssetFilterManager.
     *
     * @param AssetFilterManager $filterManager
     */
    public function setAssetFilterManager(AssetFilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * Get the AssetFilterManager
     *
     * @return AssetFilterManager
     */
    public function getAssetFilterManager()
    {
        return $this->filterManager;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        return array_keys($this->collections);
    }
}
