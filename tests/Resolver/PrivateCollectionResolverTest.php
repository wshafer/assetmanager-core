<?php

namespace AssetManager\Core\Test\Resolver;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetInterface;
use AssetManager\Core\Resolver\PrivateCollectionResolver;
use AssetManager\Core\Service\AssetFilterManager;
use AssetManager\Core\Service\MimeResolver;
use PHPUnit\Framework\TestCase;

class PrivateCollectionResolverTest extends TestCase
{
    /** @var PrivateCollectionResolver */
    protected $resolver;

    /** @var AssetFilterManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockAssetFilterManager;

    /** @var MimeResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockMimeResolver;

    public function setup()
    {
        $this->resolver = new PrivateCollectionResolver([
            'some-file-collection.css' => [
                __FILE__,
                __DIR__.'/ConcatResolverTest.php'
            ]
        ]);

        $this->mockAssetFilterManager = $this->getMockBuilder(AssetFilterManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockMimeResolver = $this->getMockBuilder(MimeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolver->setAssetFilterManager($this->mockAssetFilterManager);
        $this->resolver->setMimeResolver($this->mockMimeResolver);

        $this->assertInstanceOf(PrivateCollectionResolver::class, $this->resolver);
    }

    public function testConstructor()
    {
    }

    /**
     * @expectedException \AssetManager\Core\Exception\RuntimeException
     */
    public function testGetCollectionAssetPathNotString()
    {
        $this->mockAssetFilterManager->expects($this->never())
            ->method('setFilters');

        $this->resolver->getCollectionAsset([]);
    }

    /**
     * @expectedException \AssetManager\Core\Exception\RuntimeException
     */
    public function testGetCollectionAssetNotFound()
    {
        $this->mockAssetFilterManager->expects($this->never())
            ->method('setFilters');

        $this->resolver->getCollectionAsset('not-here.jpg');
    }

    public function testGetCollectionAssets()
    {
        $this->mockAssetFilterManager->expects($this->once())
            ->method('setFilters')
            ->willReturn(true);

        $asset = $this->resolver->getCollectionAsset(__FILE__);
        $this->assertInstanceOf(AssetInterface::class, $asset);
    }

    public function testResolve()
    {
        $this->mockAssetFilterManager->expects($this->exactly(2))
            ->method('setFilters')
            ->willReturn(true);

        $this->mockMimeResolver->expects($this->once())
            ->method('getMimeType')
            ->with('some-file-collection.css')
            ->willReturn('text/css');

        $collection = $this->resolver->resolve('some-file-collection.css');

        $this->assertInstanceOf(AssetCollection::class, $collection);

        $this->assertEquals('text/css', $collection->mimetype);
    }

    public function testResolveNotFount()
    {
        $this->mockAssetFilterManager->expects($this->never())
            ->method('setFilters');

        $this->mockMimeResolver->expects($this->never())
            ->method('getMimeType');

        $result = $this->resolver->resolve('not-here.jpg');
        $this->assertNull($result);
    }

    /**
     * @expectedException \AssetManager\Core\Exception\RuntimeException
     */
    public function testResolveConfigIsNotArray()
    {
        $this->mockAssetFilterManager->expects($this->never())
            ->method('setFilters');

        $this->mockMimeResolver->expects($this->never())
            ->method('getMimeType');

        $this->resolver = new PrivateCollectionResolver([
            'some-file-collection.css' => 'not an array'
        ]);

        $this->resolver->resolve('some-file-collection.css');
    }
}
