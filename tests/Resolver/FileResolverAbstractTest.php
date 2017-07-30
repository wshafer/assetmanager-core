<?php

namespace AssetManager\Core\Test\Resolver;

use Assetic\Asset\FileAsset;
use Assetic\Asset\HttpAsset;
use AssetManager\Core\Resolver\FileResolverAbstract;
use AssetManager\Core\Service\MimeResolver;
use PHPUnit\Framework\TestCase;

class FileResolverAbstractTest extends TestCase
{
    /** @var FileResolverAbstract */
    protected $resolver;

    /** @var MimeResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockMimeResolver;

    public function setup()
    {
        $this->resolver = $this->getMockForAbstractClass(FileResolverAbstract::class);

        $this->mockMimeResolver = $this->getMockBuilder(MimeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(FileResolverAbstract::class, $this->resolver);
    }

    public function testConstructor()
    {
    }

    public function testGetSetMimeResolver()
    {
        $this->resolver->setMimeResolver($this->mockMimeResolver);

        $result = $this->resolver->getMimeResolver();

        $this->assertEquals($this->mockMimeResolver, $result);
    }

    public function testResolveFileToHTTPAsset()
    {
        $result = $this->resolver->resolveFile('https://github.com/');
        $this->assertInstanceOf(HttpAsset::class, $result);
    }

    public function testResolveFileReturnsNullOnFileNotFound()
    {
        $result = $this->resolver->resolveFile('not-here.jpg');
        $this->assertNull($result);
    }

    public function testResolveFileReturnsNullOnDir()
    {
        $result = $this->resolver->resolveFile(sys_get_temp_dir());
        $this->assertNull($result);
    }

    public function testResolveFileToFileAsset()
    {
        $result = $this->resolver->resolveFile(__FILE__);
        $this->assertInstanceOf(FileAsset::class, $result);
    }
}