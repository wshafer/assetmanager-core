<?php

namespace AssetManager\Core\Resolver;

use Assetic\Asset\FileAsset;
use Assetic\Asset\HttpAsset;
use AssetManager\Core\Exception;
use AssetManager\Core\Service\MimeResolver;
use Traversable;
use Zend\Stdlib\ArrayUtils;

/**
 * This resolver allows you to resolve using a 1 on 1 mapping to a file.
 */
class MapResolver extends FileResolverAbstract
{
    /**
     * @var array
     */
    protected $map = array();

    /**
     * Constructor
     *
     * Instantiate and optionally populate map.
     *
     * @param array|Traversable $map
     */
    public function __construct($map = array())
    {
        $this->setMap($map);
    }

    /**
     * Set (overwrite) map
     *
     * Maps should be arrays or Traversable objects with name => path pairs
     *
     * @param  array|Traversable                  $map
     * @throws Exception\InvalidArgumentException
     */
    public function setMap($map)
    {
        if (!is_array($map) && !$map instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s: expects an array or Traversable, received "%s"',
                __METHOD__,
                (is_object($map) ? get_class($map) : gettype($map))
            ));
        }

        if ($map instanceof Traversable) {
            $map = ArrayUtils::iteratorToArray($map);
        }

        $this->map = $map;
    }

    /**
     * Retrieve the map
     *
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name)
    {
        if (!isset($this->map[$name])) {
            return null;
        }

        $asset = $this->resolveFile($this->map[$name]);

        if (!$asset) {
            return null;
        }

        $asset->mimetype = $this->getMimeResolver()->getMimeType($name);
        return $asset;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        return array_keys($this->map);
    }
}
