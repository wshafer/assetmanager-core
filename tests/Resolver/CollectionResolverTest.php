<?php

namespace AssetManager\Core\Test\Resolver;

use Assetic\Asset;
use Assetic\Asset\AssetCache;
use Assetic\Cache\CacheInterface;
use AssetManager\Core\Test\Service\CollectionsIterable;
use AssetManager\Core\Resolver\AggregateResolverAwareInterface;
use AssetManager\Core\Resolver\CollectionResolver;
use AssetManager\Core\Resolver\ResolverInterface;
use AssetManager\Core\Service\AssetFilterManager;
use AssetManager\Core\Service\MimeResolver;
use PHPUnit\Framework\TestCase;

class CollectionsResolverTest extends TestCase
{
    public function getResolverMock()
    {
        $resolver = $this->createMock(ResolverInterface::class);
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('bacon')
            ->will($this->returnValue(new Asset\FileAsset(__FILE__)));

        return $resolver;
    }

    public function testConstructor()
    {
        $resolver = new CollectionResolver;

        // Check if valid instance
        $this->assertTrue($resolver instanceof ResolverInterface);
        $this->assertTrue($resolver instanceof AggregateResolverAwareInterface);

        // Check if set to empty (null argument)
        $this->assertSame(array(), $resolver->getCollections());

        $resolver = new CollectionResolver(array(
            'key1' => array('value1'),
            'key2' => array('value2'),
        ));
        $this->assertSame(
            array(
                'key1' => array('value1'),
                'key2' => array('value2'),
            ),
            $resolver->getCollections()
        );
    }

    public function testSetGetAggregateResolver()
    {
        $resolver = new CollectionResolver;

        $aggregateResolver = $this->createMock(ResolverInterface::class);
        $aggregateResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('say')
            ->will($this->returnValue('world'));

        $resolver->setAggregateResolver($aggregateResolver);

        $this->assertEquals('world', $resolver->getAggregateResolver()->resolve('say'));
    }

    /**
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testSetAggregateResolverFails()
    {
        if (PHP_MAJOR_VERSION >= 7) {
            $this->expectException('\TypeError');
        }

        $resolver = new CollectionResolver;

        $resolver->setAggregateResolver(new \stdClass);
    }

    /**
     * Resolve
     */
    public function testResolveNoArgsEqualsNull()
    {
        $resolver = new CollectionResolver;

        $this->assertNull($resolver->resolve('bacon'));
    }

    /**
     * @expectedException \AssetManager\Core\Exception\RuntimeException
     */
    public function testResolveNonArrayCollectionException()
    {
        $resolver = new CollectionResolver(array('bacon'=>'bueno'));

        $resolver->resolve('bacon');
    }

    /**
     * @expectedException \AssetManager\Core\Exception\RuntimeException
     */
    public function testCollectionItemNonString()
    {
        $resolver = new CollectionResolver(array(
            'bacon' => array(new \stdClass())
        ));

        $resolver->resolve('bacon');
    }

    /**
     * @expectedException \AssetManager\Core\Exception\RuntimeException
     */
    public function testCouldNotResolve()
    {
        $aggregateResolver = $this->createMock(ResolverInterface::class);
        $aggregateResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('bacon')
            ->will($this->returnValue(null));

        $resolver = new CollectionResolver(array(
            'myCollection' => array('bacon')
        ));

        $resolver->setAggregateResolver($aggregateResolver);

        $resolver->resolve('myCollection');
    }

    /**
     * @expectedException \AssetManager\Core\Exception\RuntimeException
     */
    public function testResolvesToNonAsset()
    {
        $aggregateResolver = $this->createMock(ResolverInterface::class);
        $aggregateResolver
            ->expects($this->once())
            ->method('resolve')
            ->with('bacon')
            ->will($this->returnValue('invalid'));

        $resolver = new CollectionResolver(array(
            'myCollection' => array('bacon')
        ));

        $resolver->setAggregateResolver($aggregateResolver);

        $resolver->resolve('myCollection');
    }

    /**
     * @expectedException \AssetManager\Core\Exception\RuntimeException
     */
    public function testMimeTypesDontMatch()
    {
        $callbackInvocationCount = 0;
        $callback = function () use (&$callbackInvocationCount) {

            $asset1 = new Asset\StringAsset('bacon');
            $asset2 = new Asset\StringAsset('eggs');
            $asset3 = new Asset\StringAsset('Mud');

            $asset1->mimetype = 'text/plain';
            $asset2->mimetype = 'text/css';
            $asset3->mimetype = 'text/javascript';

            $callbackInvocationCount += 1;
            $assetName = "asset$callbackInvocationCount";
            return $$assetName;
        };

        $aggregateResolver = $this->createMock(ResolverInterface::class);
        $aggregateResolver
            ->expects($this->exactly(2))
            ->method('resolve')
            ->will($this->returnCallback($callback));

        $assetFilterManager = $this->createMock(AssetFilterManager::class);
        $assetFilterManager
            ->expects($this->once())
            ->method('setFilters')
            ->will($this->returnValue(null));

        $resolver = new CollectionResolver(array(
            'myCollection' => array(
                'bacon',
                'eggs',
                'mud',
            )
        ));

        $resolver->setAggregateResolver($aggregateResolver);
        $resolver->setAssetFilterManager($assetFilterManager);

        $resolver->resolve('myCollection');
    }

    public function testTwoCollectionsHasDifferentCacheKey()
    {
        $aggregateResolver = $this->createMock(ResolverInterface::class);

        //assets with same 'last modifled time'.
        $now = time();
        $bacon =  new Asset\StringAsset('bacon');
        $bacon->setLastModified($now);
        $bacon->mimetype = 'text/plain';

        $eggs =  new Asset\StringAsset('eggs');
        $eggs->setLastModified($now);
        $eggs->mimetype = 'text/plain';

        $assets = array(
            array('bacon', $bacon),
            array('eggs', $eggs),
        );

        $aggregateResolver
            ->expects($this->any())
            ->method('resolve')
            ->will($this->returnValueMap($assets));

        $resolver = new CollectionResolver(array(
            'collection1' => array(
                'bacon',
            ),
            'collection2' => array(
                'eggs',
            ),
        ));

        $assetFilterManager = new AssetFilterManager();

        $resolver->setAggregateResolver($aggregateResolver);
        $resolver->setAssetFilterManager($assetFilterManager);

        $collection1 = $resolver->resolve('collection1');
        $collection2 = $resolver->resolve('collection2');

        $cacheInterface = $this->createMock(CacheInterface::class);

        $cacheKeys = new \ArrayObject();
        $callback = function ($key) use ($cacheKeys) {
            $cacheKeys[] = $key;
            return true;
        };

        $cacheInterface
            ->expects($this->exactly(2))
            ->method('has')
            ->will($this->returnCallback($callback));

        $cacheInterface
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValue('cached content'));

        $cache1 = new AssetCache($collection1, $cacheInterface);
        $cache1->load();

        $cache2 = new AssetCache($collection2, $cacheInterface);
        $cache2->load();

        $this->assertCount(2, $cacheKeys);
        $this->assertNotEquals($cacheKeys[0], $cacheKeys[1]);
    }

    public function testSuccessResolve()
    {
        $callbackInvocationCount = 0;
        $callback = function () use (&$callbackInvocationCount) {

            $asset1 = new Asset\StringAsset('bacon');
            $asset2 = new Asset\StringAsset('eggs');
            $asset3 = new Asset\StringAsset('Mud');

            $asset1->mimetype = 'text/plain';
            $asset2->mimetype = 'text/plain';
            $asset3->mimetype = 'text/plain';

            $callbackInvocationCount += 1;
            $assetName = "asset$callbackInvocationCount";
            return $$assetName;
        };

        $aggregateResolver = $this->createMock(ResolverInterface::class);
        $aggregateResolver
            ->expects($this->exactly(3))
            ->method('resolve')
            ->will($this->returnCallback($callback));

        $resolver = new CollectionResolver(array(
            'myCollection' => array(
                'bacon',
                'eggs',
                'mud',
            )
        ));


        $assetFilterManager = new AssetFilterManager();

        $resolver->setAggregateResolver($aggregateResolver);
        $resolver->setAssetFilterManager($assetFilterManager);

        $collectionResolved = $resolver->resolve('myCollection');

        $this->assertEquals($collectionResolved->mimetype, 'text/plain');
        $this->assertTrue($collectionResolved instanceof Asset\AssetCollection);
    }

    /**
     * Test Collect returns valid list of assets
     *
     * @covers \AssetManager\Core\Resolver\CollectionResolver::collect
     */
    public function testCollect()
    {
        $collections = array(
            'myCollection' => array(
                'bacon',
                'eggs',
                'mud',
            ),
            'my/collect.ion' => array(
                'bacon',
                'eggs',
                'mud',
            ),
        );
        $resolver = new CollectionResolver($collections);

        $this->assertEquals(array_keys($collections), $resolver->collect());
    }
}
