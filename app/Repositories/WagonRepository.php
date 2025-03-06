<?php

namespace App\Repositories;

use App\Entities\Wagon;
use Redis;

class WagonRepository
{
    public const WAGON_KEY = 'wagon';
    public const WAGONS_KEY = 'wagons';
    public const COASTER_KEY = 'coaster';

    public function __construct(
        protected Redis $redis
    ) {
    }

    public function save(Wagon $wagon): Wagon
    {
        $wagonId = $wagon->getId();
        $coasterId = $wagon->getCoasterId();

        $this->redis->hMSet(self::getWagonIdKey($wagonId), $wagon->toArray());
        $this->redis->sAdd(self::getCoasterWagonsKey($coasterId), $wagonId);
        $this->redis->sAdd(self::WAGONS_KEY, $wagonId);

        return $wagon;
    }

    public function findById(string $wagonId): ?Wagon
    {
        if (!$this->redis->exists(self::getWagonIdKey($wagonId))) {
            return null;
        }

        $data = $this->redis->hGetAll(self::getWagonIdKey($wagonId));
        if (empty($data)) {
            return null;
        }

        return Wagon::fromArray($data);
    }

    /** @return Wagon[] */
    public function findByCoasterId(string $coasterId): array
    {
        $wagonIds = $this->redis->sMembers(self::getCoasterWagonsKey($coasterId));
        $wagons = [];

        foreach ($wagonIds as $wagonId) {
            $wagon = $this->findById($wagonId);
            if ($wagon) {
                $wagons[] = $wagon;
            }
        }

        return $wagons;
    }

    public function delete(string $wagonId): bool
    {
        $wagon = $this->findById($wagonId);
        if ($wagon instanceof Wagon === false) {
            return false;
        }

        $coasterId = $wagon->getCoasterId();

        $this->redis->sRem(self::getCoasterWagonsKey($coasterId), $wagonId);
        $this->redis->sRem(self::WAGONS_KEY, $wagonId);
        $this->redis->del(self::getWagonIdKey($wagonId));

        return true;
    }

    public function exists(string $wagonId): bool
    {
        return $this->redis->exists(self::getWagonIdKey($wagonId));
    }

    public function belongsToCoaster(string $wagonId, string $coasterId): bool
    {
        return $this->redis->sIsMember(self::getCoasterWagonsKey($coasterId), $wagonId);
    }

    public function countWagonsByCoasterId(string $coasterId): int
    {
        return $this->redis->sCard(self::getCoasterWagonsKey($coasterId));
    }

    private static function getWagonIdKey(string $wagonId): string
    {
        return self::WAGON_KEY . ':' . $wagonId;
    }

    private static function getCoasterWagonsKey(string $coasterId): string
    {
        return self::COASTER_KEY . ':' . $coasterId . ':' . self::WAGONS_KEY;
    }
}
