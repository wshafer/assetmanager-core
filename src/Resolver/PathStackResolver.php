<?php

namespace AssetManager\Core\Resolver;

use Assetic\Asset\FileAsset;
use Assetic\Factory\Resource\DirectoryResource;
use AssetManager\Core\Exception;
use AssetManager\Core\Service\MimeResolver;
use SplFileInfo;
use Traversable;
use Zend\Stdlib\SplStack;

/**
 * This resolver allows you to resolve from a stack of paths.
 */
class PathStackResolver extends FileResolverAbstract
{
    use LfiProtectionTrait;

    /**
     * @var SplStack
     */
    protected $paths;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->paths = new SplStack();
    }

    /**
     * Add many paths to the stack at once
     *
     * @param array|Traversable $paths
     */
    public function addPaths($paths)
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }
    }

    /**
     * Rest the path stack to the paths provided
     *
     * @param  Traversable|array                  $paths
     * @throws Exception\InvalidArgumentException
     */
    public function setPaths($paths)
    {
        if (!is_array($paths) && !$paths instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid argument provided for $paths, expecting either an array or Traversable object, "%s" given',
                is_object($paths) ? get_class($paths) : gettype($paths)
            ));
        }

        $this->clearPaths();
        $this->addPaths($paths);
    }

    /**
     * Normalize a path for insertion in the stack
     *
     * @param  string $path
     * @return string
     */
    protected function normalizePath($path)
    {
        $path = rtrim($path, '/\\');
        $path .= DIRECTORY_SEPARATOR;

        return $path;
    }

    /**
     * Add a single path to the stack
     *
     * @param  string                             $path
     * @throws Exception\InvalidArgumentException
     */
    public function addPath($path)
    {
        if (!is_string($path)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid path provided; must be a string, received %s',
                gettype($path)
            ));
        }

        $this->paths[] = $this->normalizePath($path);
    }

    /**
     * Clear all paths
     *
     * @return void
     */
    public function clearPaths()
    {
        $this->paths = new SplStack();
    }

    /**
     * Returns stack of paths
     *
     * @return SplStack
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve($name)
    {
        if ($this->isLfiProtectionOn() && preg_match('#\.\.[\\\/]#', $name)) {
            return null;
        }

        foreach ($this->getPaths() as $path) {
            $asset = $this->resolveFile($path . $name);

            if (!$asset) {
                return null;
            }

            $asset->mimetype = $this->getMimeResolver()->getMimeType($name);
            return $asset;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $collection = array();
        foreach ($this->getPaths() as $path) {
            $locations = new SplStack();
            $pathInfo = new SplFileInfo($path);
            $locations->push($pathInfo);
            $basePath = $this->normalizePath($pathInfo->getRealPath());

            while (!$locations->isEmpty()) {
                /** @var SplFileInfo $pathInfo */
                $pathInfo = $locations->pop();
                if (!$pathInfo->isReadable()) {
                    continue;
                }
                if ($pathInfo->isDir()) {
                    $dir = new DirectoryResource($pathInfo->getRealPath());
                    foreach ($dir as $resource) {
                        $locations->push(new SplFileInfo($resource));
                    }
                } elseif (!isset($collection[$pathInfo->getPath()])) {
                    $collection[] = substr($pathInfo->getRealPath(), strlen($basePath));
                }
            }
        }

        return $collection;
    }
}
