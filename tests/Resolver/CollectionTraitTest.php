<?php

namespace AssetManager\Core\Test\Resolver;

use AssetManager\Core\Resolver\CollectionTrait;
use AssetManager\Core\Test\Service\CollectionsIterable;
use PHPUnit\Framework\TestCase;

class CollectionTraitTest extends TestCase
{
    public function testSetCollections()
    {
        /** @var CollectionTrait $resolver */
        $resolver = $this->getMockForTrait(CollectionTrait::class);
        $collArr  = array(
            'key1' => array('value1'),
            'key2' => array('value2'),
        );

        $resolver->setCollections($collArr);

        $this->assertSame(
            $collArr,
            $resolver->getCollections()
        );

        // overwrite
        $collArr = array(
            'key3' => array('value3'),
            'key4' => array('value4'),
        );

        $resolver->setCollections($collArr);

        $this->assertSame(
            $collArr,
            $resolver->getCollections()
        );


        // Overwrite with traversable
        $resolver->setCollections(new CollectionsIterable());

        $collArr = array(
            'collectionName1' => array(
                'collection 1.1',
                'collection 1.2',
                'collection 1.3',
                'collection 1.4',
            ),
            'collectionName2' => array(
                'collection 2.1',
                'collection 2.2',
                'collection 2.3',
                'collection 2.4',
            ),
            'collectionName3' => array(
                'collection 3.1',
                'collection 3.2',
                'collection 3.3',
                'collection 3.4',
            )
        );

        $this->assertEquals($collArr, $resolver->getCollections());
    }

    /**
     * @expectedException \AssetManager\Core\Exception\InvalidArgumentException
     */
    public function testSetCollectionFailsObject()
    {
        /** @var CollectionTrait $resolver */
        $resolver = $this->getMockForTrait(CollectionTrait::class);

        $resolver->setCollections(new \stdClass);
    }

    /**
     * @expectedException \AssetManager\Core\Exception\InvalidArgumentException
     */
    public function testSetCollectionFailsString()
    {
        /** @var CollectionTrait $resolver */
        $resolver = $this->getMockForTrait(CollectionTrait::class);

        $resolver->setCollections('invalid');
    }
}
