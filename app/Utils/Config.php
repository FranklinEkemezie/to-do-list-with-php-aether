<?php

namespace PHPAether\Utils;

use PHPAether\Exceptions\NotFoundException\ConfigNotFoundException;

class Config
{
    protected static $CONFIG = [];
    public static function setUp(string $configFile): void
    {
        $configArr = require $configFile;

        foreach ($configArr as $config) {
            foreach ($config as $key => $value) {
                self::$CONFIG[$key] = $value;
            }
        }
    }

    /**
     * @throws ConfigNotFoundException when the configuration is not found
     */
    public static function get(string $key): mixed
    {
        return static::$CONFIG[$key] ?? throw new ConfigNotFoundException(
            "Could not find config with key: $key"
        );
    }
}