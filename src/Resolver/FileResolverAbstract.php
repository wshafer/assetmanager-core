<?php

namespace AssetManager\Core\Resolver;

use Assetic\Asset\FileAsset;
use Assetic\Asset\HttpAsset;
use AssetManager\Core\Service\MimeResolver;

abstract class FileResolverAbstract implements ResolverInterface, MimeResolverAwareInterface
{
    /**
     * @var MimeResolver The mime resolver.
     */
    protected $mimeResolver;

    abstract public function resolve($path);

    /**
     * {@inheritDoc}
     */
    public function resolveFile($file)
    {
        if (filter_var($file, FILTER_VALIDATE_URL)) {
            return $this->getHtmlAsset($file);
        }

        return $this->getFileAsset($file);
    }

    /**
     * Get an HTML Asset
     *
     * @param string $filePath
     *
     * @return HttpAsset
     */
    protected function getHtmlAsset($filePath)
    {
        return new HttpAsset($filePath);
    }

    /**
     * Get a File Asset
     *
     * @param string $filePath
     *
     * @return FileAsset|null
     */
    protected function getFileAsset($filePath)
    {
        $file = new \SplFileInfo($filePath);

        if (!$file->isReadable() || $file->isDir()) {
            return null;
        }

        $filePath = $file->getRealPath();

        return new FileAsset($filePath);
    }

    /**
     * Set the mime resolver
     *
     * @param MimeResolver $resolver
     */
    public function setMimeResolver(MimeResolver $resolver)
    {
        $this->mimeResolver = $resolver;
    }

    /**
     * Get the mime resolver
     *
     * @return MimeResolver
     */
    public function getMimeResolver()
    {
        return $this->mimeResolver;
    }
}
