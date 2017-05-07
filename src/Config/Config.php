<?php

namespace AssetManager\Core\Config;

/**
 * Allows attaching modules to get the core configuration
 */
class Config
{
    public static function getConfig()
    {
        return require __DIR__ . '/../../config/module.config.php';
    }
}
