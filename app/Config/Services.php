<?php

namespace Config;

use App\Libraries\RedisClient;
use App\Repositories\CoasterRepository;
use App\Repositories\WagonRepository;
use App\Services\CoasterService;
use App\Services\MonitoringService;
use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    public static function redisClient(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('redisClient');
        }

        /** @var Redis $config */
        $config = config('Redis');

        return RedisClient::getClient($config);
    }

    public static function coasterRepository(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('coasterRepository');
        }

        return new CoasterRepository(self::redisClient());
    }

    public static function wagonRepository(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('wagonRepository');
        }

        return new WagonRepository(self::redisClient());
    }

    public static function coasterService(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('coasterService');
        }

        return new CoasterService(
            static::coasterRepository(),
            static::wagonRepository(),
            static::validation()
        );
    }

    public static function monitoringService(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('monitoringService');
        }

        return new MonitoringService(
            static::coasterRepository(),
            static::wagonRepository(),
            static::logger()
        );
    }
}
