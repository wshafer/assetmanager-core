<?php

namespace AssetManager\Core\Resolver;

use ArrayAccess;
use Assetic\Asset\FileAsset;
use Assetic\Factory\Resource\DirectoryResource;
use AssetManager\Core\Exception;
use AssetManager\Core\Service\MimeResolver;
use SplFileInfo;
use Traversable;
use Zend\Stdlib\PriorityQueue;
use Zend\Stdlib\SplStack;

/**
 * This resolver allows you to resolve from a multitude of prioritized paths.
 */
class PrioritizedPathsResolver extends FileResolverAbstract
{
    /**
     * @var PriorityQueue|ResolverInterface[]
     */
    protected $paths;

    /**
     * Flag indicating whether or not LFI protection for rendering view scripts is enabled
     *
     * @var bool
     */
    protected $lfiProtectionOn = true;

    /**
     * Constructor.
     * Construct object and set a new PriorityQueue.
     */
    public function __construct()
    {
        $this->paths = new PriorityQueue();
    }

    /**
     * {@inheritDoc}
     */
    public function addPath($path)
    {
        if (is_string($path)) {
            $this->paths->insert($this->normalizePath($path), 1);

            return;
        }

        if (!is_array($path) && !$path instanceof ArrayAccess) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Provided path must be an array or an instance of ArrayAccess, %s given',
                is_object($path) ? get_class($path) : gettype($path)
            ));
        }

        if (isset($path['priority']) && isset($path['path'])) {
            $this->paths->insert($this->normalizePath($path['path']), $path['priority']);

            return;
        }

        throw new Exception\InvalidArgumentException('Provided array must contain both keys "priority" and "path"');
    }

    /**
     * {@inheritDoc}
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * {@inheritDoc}
     */
    public function clearPaths()
    {
        $this->paths = new PriorityQueue();
    }

     /**
      * Add many paths to the stack at once
      *
      * @param  array|Traversable $paths
      *
      * @return void
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
     *
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
     *
     * @return string
     */
    protected function normalizePath($path)
    {
        $path = rtrim($path, '/\\');
        $path .= DIRECTORY_SEPARATOR;

        return $path;
    }

    /**
     * Set LFI protection flag
     *
     * @param  bool $flag
     * @return void
     */
    public function setLfiProtection($flag)
    {
        $this->lfiProtectionOn = (bool) $flag;
    }

    /**
     * Return status of LFI protection flag
     *
     * @return bool
     */
    public function isLfiProtectionOn()
    {
        return $this->lfiProtectionOn;
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
                continue;
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
                    throw new Exception\RuntimeException(sprintf('%s is not readable.', $pathInfo->getPath()));
                }
                if ($pathInfo->isDir()) {
                    foreach (new DirectoryResource($pathInfo->getRealPath()) as $resource) {
                        $locations->push(new SplFileInfo($resource));
                    }
                } else {
                    $collection[] = substr($pathInfo->getRealPath(), strlen($basePath));
                }
            }
        }

        return array_unique($collection);
    }
}
