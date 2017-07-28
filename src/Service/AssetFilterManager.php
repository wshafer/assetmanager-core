<?php

namespace AssetManager\Core\Service;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;
use AssetManager\Core\Exception;
use AssetManager\Core\Resolver\MimeResolverAwareInterface;
use Psr\Container\ContainerInterface;

class AssetFilterManager
{
    /**
     * @var array Filter configuration.
     */
    protected $config;

    /**
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * @var FilterInterface[] Filters already instantiated
     */
    protected $filterInstances = array();
    
    /**
     * Construct the AssetFilterManager
     *
     * @param   array $config
     */
    public function __construct(array $config = array())
    {
        $this->setConfig($config);
    }

    /**
     * Get the filter configuration.
     *
     * @return  array
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the filter configuration.
     *
     * @param array $config
     */
    protected function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * See if there are filters for the asset, and if so, set them.
     *
     * @param   string          $path
     * @param   AssetInterface  $asset
     *
     * @throws Exception\RuntimeException on invalid filters
     */
    public function setFilters($path, AssetInterface $asset)
    {
        $filters = $this->getFilters($path, $asset);

        foreach ($filters as $filter) {
            $this->setFilter($filter, $asset);
        }
    }

    /**
     * Get the filters from config based on path, mimetype or extension.
     *
     * @param $path
     * @param AssetInterface $asset
     * @return array|mixed
     */
    protected function getFilters($path, AssetInterface $asset)
    {
        $config = $this->getConfig();

        if (!empty($config[$path])) {
            return $config[$path];
        }

        $extension = strtolower(pathinfo($asset->getSourcePath(), PATHINFO_EXTENSION));

        if (!empty($config[$extension])) {
            return $config[$extension];
        }

        return [];
    }

    protected function setFilter($filter, AssetInterface $asset)
    {
        if (is_null($filter)) {
            return;
        }

        if (!empty($filter['filter'])) {
            $this->ensureByFilter($asset, $filter['filter']);
            return;
        }

        if (!empty($filter['service'])) {
            $this->ensureByService($asset, $filter['service']);
            return;
        }

        throw new Exception\RuntimeException(
            'Invalid filter supplied. Expected Filter or Service.'
        );
    }

    /**
     * Ensure that the filters as service are set.
     *
     * @param   AssetInterface  $asset
     * @param   string          $service    A valid service name.
     * @throws  Exception\RuntimeException
     */
    protected function ensureByService(AssetInterface $asset, $service)
    {
        if (is_string($service)) {
            $this->ensureByFilter($asset, $this->getContainer()->get($service));
        } else {
            throw new Exception\RuntimeException(
                'Unexpected service provided. Expected string or callback.'
            );
        }
    }

    /**
     * Ensure that the filters as filter are set.
     *
     * @param   AssetInterface  $asset
     * @param   mixed           $filter    Either an instance of FilterInterface or a classname.
     * @throws  Exception\RuntimeException
     */
    protected function ensureByFilter(AssetInterface $asset, $filter)
    {
        if ($filter instanceof FilterInterface) {
            $filterInstance = $filter;
            $asset->ensureFilter($filterInstance);

            return;
        }

        $filterClass = $filter;

        if (!is_subclass_of($filterClass, 'Assetic\Filter\FilterInterface', true)) {
            $filterClass .= (substr($filterClass, -6) === 'Filter') ? '' : 'Filter';
            $filterClass  = 'Assetic\Filter\\' . $filterClass;
        }

        if (!class_exists($filterClass)) {
            throw new Exception\RuntimeException(
                'No filter found for ' . $filter
            );
        }

        if (!isset($this->filterInstances[$filterClass])) {
            $this->filterInstances[$filterClass] = new $filterClass();
        }

        $filterInstance = $this->filterInstances[$filterClass];

        $asset->ensureFilter($filterInstance);
    }

    /**
     * {@inheritDoc}
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
