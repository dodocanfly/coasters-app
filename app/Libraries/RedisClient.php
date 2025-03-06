<?php

namespace App\Libraries;

use Config\Redis as RedisConfig;
use Redis;

class RedisClient
{
    protected Redis $redis;

    public function __construct(string $host = 'localhost', int $port = 6379, int $database = 0, string $password = '', string $username = '', int $timeout = 0)
    {
        $this->redis = new Redis();

        if (!empty($password) && !empty($username)) {
            $this->redis->auth([$username, $password]);
        } elseif (!empty($password)) {
            $this->redis->auth([$password]);
        }

        $this->redis->connect($host, $port, $timeout);

        $this->redis->select($database);
    }

    public static function getClient(RedisConfig $config): Redis
    {
        $redis = new self(
            $config->default['host'],
            $config->default['port'],
            $config->default['database'],
            $config->default['password'] ?? '',
            $config->default['username'] ?? '',
            $config->default['timeout'] ?? 0,
        );

        return $redis->redis;
    }
}
