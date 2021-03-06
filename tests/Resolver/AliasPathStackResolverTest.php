<?php

namespace AssetManager\Core\Test\Resolver;

use Assetic\Asset;
use AssetManager\Core\Resolver\AliasPathStackResolver;
use AssetManager\Core\Service\MimeResolver;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for the Alias Path Stack Resolver
 */
class AliasPathStackResolverTest extends TestCase
{
    /**
     * Test constructor passes
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::__construct
     */
    public function testConstructor()
    {
        $aliases = array(
            'alias1' => __DIR__ . DIRECTORY_SEPARATOR,
        );

        $resolver = new AliasPathStackResolver($aliases);

        $reflectionClass = new \ReflectionClass(AliasPathStackResolver::class);
        $property        = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $this->assertEquals(
            $aliases,
            $property->getValue($resolver)
        );
    }

    /**
     * Test constructor fails when aliases passed in is not an array
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::__construct
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testConstructorFail()
    {
        if (PHP_MAJOR_VERSION >= 7) {
            $this->expectException('\TypeError');
        }

        new AliasPathStackResolver('this_should_fail');
    }

    /**
     * Test add alias method.
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::addAlias
     */
    public function testAddAlias()
    {
        $resolver        = new AliasPathStackResolver(array());
        $reflectionClass = new \ReflectionClass(AliasPathStackResolver::class);
        $addAlias        = $reflectionClass->getMethod('addAlias');

        $addAlias->setAccessible(true);

        $property = $reflectionClass->getProperty('aliases');

        $property->setAccessible(true);

        $addAlias->invoke($resolver, 'alias', 'path');

        $this->assertEquals(
            array('alias' => 'path' . DIRECTORY_SEPARATOR),
            $property->getValue($resolver)
        );
    }

    /**
     * Test addAlias fails with bad key
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::addAlias
     * @expectedException \AssetManager\Core\Exception\InvalidArgumentException
     */
    public function testAddAliasFailsWithBadKey()
    {
        $resolver        = new AliasPathStackResolver(array());
        $reflectionClass = new \ReflectionClass(AliasPathStackResolver::class);
        $addAlias        = $reflectionClass->getMethod('addAlias');

        $addAlias->setAccessible(true);

        $property = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $addAlias->invoke($resolver, null, 'path');
    }

    /**
     * Test addAlias fails with bad Path
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::addAlias
     * @expectedException \AssetManager\Core\Exception\InvalidArgumentException
     */
    public function testAddAliasFailsWithBadPath()
    {
        $resolver = new AliasPathStackResolver(array());

        $reflectionClass = new \ReflectionClass(AliasPathStackResolver::class);

        $addAlias = $reflectionClass->getMethod('addAlias');
        $addAlias->setAccessible(true);

        $property = $reflectionClass->getProperty('aliases');
        $property->setAccessible(true);

        $addAlias->invoke($resolver, 'alias', null);
    }

    /**
     * Test normalize path
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::normalizePath
     */
    public function testNormalizePath()
    {
        $resolver        = new AliasPathStackResolver(array());
        $reflectionClass = new \ReflectionClass(AliasPathStackResolver::class);
        $addAlias        = $reflectionClass->getMethod('normalizePath');

        $addAlias->setAccessible(true);

        $result = $addAlias->invoke($resolver, 'somePath\/\/\/');

        $this->assertEquals(
            'somePath' . DIRECTORY_SEPARATOR,
            $result
        );
    }

    /**
     * Test Set Mime Resolver Only Accepts a mime Resolver
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::setMimeResolver
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::getMimeResolver
     */
    public function testGetAndSetMimeResolver()
    {
        $mimeReolver = $this->getMockBuilder(MimeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__));

        $resolver->setMimeResolver($mimeReolver);

        $returned = $resolver->getMimeResolver();

        $this->assertEquals($mimeReolver, $returned);
    }

    /**
     * Test Set Mime Resolver Only Accepts a mime Resolver
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::setMimeResolver
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetMimeResolverFailObject()
    {
        if (PHP_MAJOR_VERSION >= 7) {
            $this->expectException('\TypeError');
        }

        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__));
        $resolver->setMimeResolver(new \stdClass());
    }



    /**
     * Test Resolve returns valid asset
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::resolve
     */
    public function testResolve()
    {
        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__));
        $this->assertTrue($resolver instanceof AliasPathStackResolver);
        $mimeResolver = new MimeResolver();
        $resolver->setMimeResolver($mimeResolver);
        $fileAsset           = new Asset\FileAsset(__FILE__);
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);
        $this->assertEquals($fileAsset, $resolver->resolve('my/alias/' . basename(__FILE__)));
        $this->assertNull($resolver->resolve('i-do-not-exist.php'));
    }

    /**
     * Test Resolve returns valid asset
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::resolve
     */
    public function testResolveWhenAliasStringDoesnotContainTrailingSlash()
    {
        $resolver = new AliasPathStackResolver(array('my/alias' => __DIR__));
        $mimeResolver = new MimeResolver();
        $resolver->setMimeResolver($mimeResolver);
        $fileAsset           = new Asset\FileAsset(__FILE__);
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);
        $this->assertEquals($fileAsset, $resolver->resolve('my/alias/' . basename(__FILE__)));
    }

    /**
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::resolve
     */
    public function testResolveWhenAliasExistsInPath()
    {
        $resolver     = new AliasPathStackResolver(array('AliasPathStackResolverTest/' => __DIR__));
        $mimeResolver = new MimeResolver();
        $resolver->setMimeResolver($mimeResolver);
        $fileAsset           = new Asset\FileAsset(__FILE__);
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);
        $this->assertEquals($fileAsset, $resolver->resolve('AliasPathStackResolverTest/' . basename(__FILE__)));

        $map = array(
            'AliasPathStackResolverTest/' => __DIR__,
            'prefix/AliasPathStackResolverTest/' =>  __DIR__
        );
        $resolver = new AliasPathStackResolver($map);
        $resolver->setMimeResolver(new MimeResolver());
        $fileAsset           = new Asset\FileAsset(__FILE__);
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);
        $this->assertEquals($fileAsset, $resolver->resolve('prefix/AliasPathStackResolverTest/' . basename(__FILE__)));
    }

    /**
     * Test that resolver will not resolve directories
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::resolve
     */
    public function testWillNotResolveDirectories()
    {
        $resolver = new AliasPathStackResolver(array('my/alias/' => __DIR__ . '/AssetManagerTest'));
        $this->assertNull($resolver->resolve('my/alias/' . basename(__DIR__)));
    }

    /**
     * BUG: https://github.com/RWOverdijk/AssetManager/issues/194
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::resolve
     */
    public function testResolveForFalsePositives()
    {
        $map = array(
            'images/'        => __DIR__,
            'public-images/' =>  __DIR__.'/../Config'
        );

        $resolver            = new AliasPathStackResolver($map);
        $mimeResolver        = new MimeResolver();
        $fileAsset           = new Asset\FileAsset(realpath(__DIR__.'/../Config/ConfigTest.php'));
        $fileAsset->mimetype = $mimeResolver->getMimeType(__FILE__);

        $resolver->setMimeResolver($mimeResolver);
        $this->assertEquals($fileAsset, $resolver->resolve('public-images/ConfigTest.php'));
    }

    /**
     * Test Lfi Protection
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::resolve
     */
    public function testLfiProtection()
    {
        $mimeResolver = new MimeResolver();
        $resolver     = new AliasPathStackResolver(array('my/alias/' => __DIR__));
        $resolver->setMimeResolver($mimeResolver);

        // should be on by default
        $this->assertTrue($resolver->isLfiProtectionOn());

        $this->assertNull($resolver->resolve(
            '..' . DIRECTORY_SEPARATOR . basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__)
        ));

        $resolver->setLfiProtection(false);

        $this->assertEquals(
            file_get_contents(__FILE__),
            $resolver->resolve(
                'my/alias/..' . DIRECTORY_SEPARATOR . basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__)
            )->dump()
        );
    }

    /**
     * Test Collect returns valid list of assets
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::collect
     */
    public function testCollect()
    {
        $alias    = 'my/alias/';
        $resolver = new AliasPathStackResolver(array($alias => __DIR__));

        $this->assertContains($alias . basename(__FILE__), $resolver->collect());
        $this->assertNotContains($alias . 'i-do-not-exist.php', $resolver->collect());
    }

    /**
     * Test Collect returns valid list of assets
     *
     * @covers \AssetManager\Core\Resolver\AliasPathStackResolver::collect
     */
    public function testCollectDirectory()
    {
        $alias    = 'my/alias/';
        $resolver = new AliasPathStackResolver(array($alias => realpath(__DIR__ . '/../')));
        $dir      = substr(__DIR__, strrpos(__DIR__, DIRECTORY_SEPARATOR, 0) + 1);

        $this->assertContains($alias . $dir . DIRECTORY_SEPARATOR . basename(__FILE__), $resolver->collect());
        $this->assertNotContains($alias . $dir . DIRECTORY_SEPARATOR . 'i-do-not-exist.php', $resolver->collect());
    }
}
