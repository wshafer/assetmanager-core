<?php

namespace AssetManager\Core\Resolver;

use Assetic\Factory\Resource\DirectoryResource;
use AssetManager\Core\Exception;
use SplFileInfo;
use Zend\Stdlib\SplStack;

/**
 * This resolver allows you to resolve from a stack of aliases to a path.
 */
class AliasPathStackResolver extends FileResolverAbstract
{
    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * Flag indicating whether or not LFI protection for rendering view scripts is enabled
     *
     * @var bool
     */
    protected $lfiProtectionOn = true;

    /**
     * Constructor
     *
     * Populate the array stack with a list of aliases and their corresponding paths
     *
     * @param  array                              $aliases
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(array $aliases)
    {
        foreach ($aliases as $alias => $path) {
            $this->addAlias($alias, $path);
        }
    }

    /**
     * Add a single alias to the stack
     *
     * @param  string                             $alias
     * @param  string                             $path
     *
     * @throws Exception\InvalidArgumentException
     */
    private function addAlias($alias, $path)
    {
        if (!is_string($path)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid path provided; must be a string, received %s',
                gettype($path)
            ));
        }

        if (!is_string($alias)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid alias provided; must be a string, received %s',
                gettype($alias)
            ));
        }

        $this->aliases[$alias] = $this->normalizePath($path);
    }

    /**
     * Normalize a path for insertion in the stack
     *
     * @param  string $path
     *
     * @return string
     */
    private function normalizePath($path)
    {
        return rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
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

        foreach ($this->aliases as $alias => $path) {
            if (strpos($name, $alias) !== 0) {
                continue;
            }

            $filename = substr_replace($name, '', 0, strlen($alias));

            $asset = $this->resolveFile($path . $filename);

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
        $collection = [];

        foreach ($this->aliases as $alias => $path) {
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
                    $collection[] = $alias . substr($pathInfo->getRealPath(), strlen($basePath));
                }
            }
        }

        return array_unique($collection);
    }
}
