<?php

namespace AssetManager\Core\Test\Resolver;

use AssetManager\Core\Resolver\LfiProtectionTrait;
use PHPUnit\Framework\TestCase;

class LfiProtectionTraitTest extends TestCase
{
    /**
     * Test Lfi Protection Flag Defaults to true
     *
     * @covers \AssetManager\Core\Resolver\LfiProtectionTrait::isLfiProtectionOn
     */
    public function testLfiProtectionFlagDefaultsTrue()
    {
        /** @var LfiProtectionTrait $resolver */
        $resolver = $this->getMockForTrait(LfiProtectionTrait::class);
        $returned = $resolver->isLfiProtectionOn();

        $this->assertTrue($returned);
    }

    /**
     * Test Get and Set of Lfi Protection Flag
     *
     * @covers \AssetManager\Core\Resolver\LfiProtectionTrait::setLfiProtection
     * @covers \AssetManager\Core\Resolver\LfiProtectionTrait::isLfiProtectionOn
     */
    public function testGetAndSetOfLfiProtectionFlag()
    {
        /** @var LfiProtectionTrait $resolver */
        $resolver = $this->getMockForTrait(LfiProtectionTrait::class);
        $resolver->setLfiProtection(true);
        $returned = $resolver->isLfiProtectionOn();

        $this->assertTrue($returned);

        $resolver->setLfiProtection(false);
        $returned = $resolver->isLfiProtectionOn();

        $this->assertFalse($returned);
    }
}
