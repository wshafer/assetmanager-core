<?php

namespace AssetManager\Core\Test\Resolver;

use ArrayObject;
use Assetic\Asset;
use AssetManager\Core\Exception\InvalidArgumentException;
use AssetManager\Core\Resolver\MimeResolverAwareInterface;
use AssetManager\Core\Resolver\PathStackResolver;
use AssetManager\Core\Resolver\ResolverInterface;
use AssetManager\Core\Service\MimeResolver;
use PHPUnit\Framework\TestCase;

class PathStackResolverTest extends TestCase
{
    public function testConstructor()
    {
        $resolver = new PathStackResolver();
        $this->assertEmpty($resolver->getPaths()->toArray());

        $resolver->addPaths(array(__DIR__));
        $this->assertEquals(array(__DIR__ . DIRECTORY_SEPARATOR), $resolver->getPaths()->toArray());

        $resolver->clearPaths();
        $this->assertEquals(array(), $resolver->getPaths()->toArray());

        $this->assertTrue($resolver instanceof MimeResolverAwareInterface);
        $this->assertTrue($resolver instanceof ResolverInterface);
        $mimeResolver = new MimeResolver;

        $resolver->setMimeResolver($mimeResolver);

        $this->assertEquals($mimeResolver, $resolver->getMimeResolver());
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetMimeResolverFailObject()
    {
        if (PHP_MAJOR_VERSION >= 7) {
            $this->expectException('\TypeError');
        }

        $resolver = new PathStackResolver();
        $resolver->setMimeResolver(new \stdClass());
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetMimeResolverFailString()
    {
        if (PHP_MAJOR_VERSION >= 7) {
            $this->expectException('\TypeError');
        }

        $resolver = new PathStackResolver();
        $resolver->setMimeResolver('invalid');
    }

    public function testSetPaths()
    {
        $resolver = new PathStackResolver();
        $resolver->setPaths(array('dir2', 'dir1'));
        // order inverted because of how a stack is traversed
        $this->assertSame(
            array('dir1' . DIRECTORY_SEPARATOR, 'dir2' . DIRECTORY_SEPARATOR),
            $resolver->getPaths()->toArray()
        );

        $paths = new ArrayObject(array(
            'dir4',
            'dir3',
        ));
        $resolver->setPaths($paths);
        $this->assertSame(
            array('dir3' . DIRECTORY_SEPARATOR, 'dir4' . DIRECTORY_SEPARATOR),
            $resolver->getPaths()->toArray()
        );

        $this->setExpectedException(InvalidArgumentException::class);
        $resolver->setPaths('invalid');
    }

    public function testResolve()
    {
        $resolver = new PathStackResolver();
        $this->assertTrue($resolver instanceof PathStackResolver);

        $mimeResolver = new MimeResolver;
        $resolver->setMimeResolver($mimeResolver);

        $resolver->addPath(__DIR__);

        $fileAsset = new Asset\FileAsset(__FILE__);
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);

        $this->assertEquals($fileAsset, $resolver->resolve(basename(__FILE__)));
        $this->assertNull($resolver->resolve('i-do-not-exist.php'));
    }

    public function testWillNotResolveDirectories()
    {
        $resolver = new PathStackResolver();
        $resolver->addPath(__DIR__ . '/AssetManagerTest');

        $this->assertNull($resolver->resolve(basename(__DIR__)));
    }

    public function testLfiProtection()
    {
        $mimeResolver = new MimeResolver;
        $resolver = new PathStackResolver;
        $resolver->setMimeResolver($mimeResolver);

        // should be on by default
        $this->assertTrue($resolver->isLfiProtectionOn());
        $resolver->addPath(__DIR__);

        $this->assertNull($resolver->resolve(
            '..' . DIRECTORY_SEPARATOR . basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__)
        ));

        $resolver->setLfiProtection(false);

        $this->assertEquals(
            file_get_contents(__FILE__),
            $resolver->resolve(
                '..' . DIRECTORY_SEPARATOR . basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__)
            )->dump()
        );
    }

    public function testWillRefuseInvalidPath()
    {
        $resolver = new PathStackResolver();
        $this->setExpectedException(InvalidArgumentException::class);
        $resolver->addPath(null);
    }

    /**
     * Test Collect returns valid list of assets
     *
     * @covers \AssetManager\Core\Resolver\PathStackResolver::collect
     */
    public function testCollect()
    {
        $resolver = new PathStackResolver();
        $resolver->addPath(__DIR__);

        $this->assertContains(basename(__FILE__), $resolver->collect());
        $this->assertNotContains('i-do-not-exist.php', $resolver->collect());
    }

    /**
     * Test Collect returns valid list of assets
     *
     * @covers \AssetManager\Core\Resolver\PathStackResolver::collect
     */
    public function testCollectDirectory()
    {
        $resolver = new PathStackResolver();
        $resolver->addPath(realpath(__DIR__ . '/../'));
        $dir = substr(__DIR__, strrpos(__DIR__, '/', 0) + 1);

        $this->assertContains($dir . DIRECTORY_SEPARATOR . basename(__FILE__), $resolver->collect());
        $this->assertNotContains($dir . DIRECTORY_SEPARATOR . 'i-do-not-exist.php', $resolver->collect());
    }
}
