<?php

namespace AssetManager\Core\Test\Config;

use AssetManager\Core\Config\Config;
use PHPUnit\Framework\TestCase;

/**
 * Test to ensure the config class is returning
 *
 * @package AssetManagerTest\Config
 */
class ConfigTest extends TestCase
{
    public function testGetConfig()
    {
        $config = Config::getConfig();
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertArrayHasKey('asset_manager', $config);
    }
}
