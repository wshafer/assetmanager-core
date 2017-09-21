<?php

use AssetManager\Core\Resolver;
use AssetManager\Core\Resolver\ResolverInterface;
use AssetManager\Core\Service\MimeResolver;
use AssetManager\Core\Service\AssetFilterManagerAwareInterface;

class InterfaceTestResolver implements
    Resolver\ResolverInterface,
    Resolver\AggregateResolverAwareInterface,
    Resolver\MimeResolverAwareInterface,
    AssetFilterManagerAwareInterface
{
    public $calledFilterManager;
    public $calledMime;
    public $calledAggregate;

    public function resolve($path)
    {
    }

    public function collect()
    {
    }

    public function getAggregateResolver()
    {

    }

    public function setAggregateResolver(ResolverInterface $resolver)
    {
        $this->calledAggregate = true;
    }

    public function setMimeResolver(MimeResolver $resolver)
    {
        $this->calledMime = true;
    }

    public function getMimeResolver()
    {
        return $this->calledMime;
    }

    public function getAssetFilterManager()
    {

    }

    public function setAssetFilterManager(\AssetManager\Core\Service\AssetFilterManager $filterManager)
    {
        $this->calledFilterManager = true;
    }
}
