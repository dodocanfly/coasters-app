<?php

namespace App\Repositories;

use App\Entities\Coaster;
use Redis;

class CoasterRepository
{
    public const COASTER_KEY = 'coaster';
    public const COASTERS_KEY = 'coasters';

    public function __construct(
        protected Redis $redis
    ) {
    }

    public function save(Coaster $coaster): Coaster
    {
        $coasterId = $coaster->getId();

        $this->redis->hMSet(self::getCoasterIdKey($coasterId), $coaster->toArray());
        $this->redis->sAdd(self::COASTERS_KEY, $coasterId);

        return $coaster;
    }

    public function findById(string $coasterId): ?Coaster
    {
        if (!$this->redis->exists(self::getCoasterIdKey($coasterId))) {
            return null;
        }

        $data = $this->redis->hGetAll(self::getCoasterIdKey($coasterId));
        if (empty($data)) {
            return null;
        }

        return Coaster::fromArray($data);
    }

    /** @return Coaster[] */
    public function findAll(): array
    {
        $coasterIds = $this->redis->sMembers(self::COASTERS_KEY);
        $coasters = [];

        foreach ($coasterIds as $coasterId) {
            $coasters[] = $this->findById($coasterId);
        }

        return array_filter($coasters);
    }

    public function exists(string $coasterId): bool
    {
        return $this->redis->sIsMember(self::COASTERS_KEY, $coasterId);
    }

    public static function getCoasterIdKey(string $coasterId): string
    {
        return self::COASTER_KEY . ':' . $coasterId;
    }
}
