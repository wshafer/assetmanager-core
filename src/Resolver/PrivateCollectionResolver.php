<?php

namespace AssetManager\Core\Resolver;

use Assetic\Asset\AssetCollection;
use AssetManager\Core\Exception;
use AssetManager\Core\Service\AssetFilterManager;
use AssetManager\Core\Service\AssetFilterManagerAwareInterface;
use Traversable;

/**
 * This resolver allows the resolving of collections with no addition mapping.
 * Unlike the collection resolver, the assets mapped here can not
 * be accessed outside the requested filename.
 */
class PrivateCollectionResolver extends FileResolverAbstract implements AssetFilterManagerAwareInterface
{
    use CollectionTrait;

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
     * {@inheritDoc}
     */
    public function resolve($name)
    {
        $collectionMap = $this->getCollections();

        if (!isset($collectionMap[$name])) {
            return null;
        }

        if (!is_array($collectionMap[$name])) {
            throw new Exception\RuntimeException(
                "Collection with name $name is not an an array."
            );
        }

        $collection = new AssetCollection;
        $collection->setTargetPath($name);

        foreach ($collectionMap[$name] as $path) {
            $collection->add($this->getCollectionAsset($path));
        }

        $collection->mimetype = $this->getMimeResolver()->getMimeType($name);

        return $collection;
    }

    /**
     * @param string $path
     *
     * @return \Assetic\Asset\FileAsset|\Assetic\Asset\HttpAsset|null
     */
    public function getCollectionAsset($path)
    {
        if (!is_string($path)) {
            throw new Exception\RuntimeException(
                'Asset should be of type string. got ' . gettype($path)
            );
        }

        $asset = $this->resolveFile($path);

        if (empty($asset)) {
            throw new Exception\RuntimeException(
                'Unable to locate file: '.$path
            );
        }

        $this->getAssetFilterManager()->setFilters($path, $asset);

        return $asset;
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
