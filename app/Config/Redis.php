<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Redis extends BaseConfig
{
    public array $default = [
        'host'     => 'redis',
        'port'     => 6379,
        'password' => null,
        'username' => null,
        'database' => 0,
        'timeout'  => 0,
    ];

    public static function getUri(): string
    {
        $redis = new static();

        return "redis://{$redis->default['host']}:{$redis->default['port']}/{$redis->default['database']}";
    }
}
