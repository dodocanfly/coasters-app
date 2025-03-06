<?php

namespace App\Entities;

class Wagon
{
    public const KEY_ID = 'id';
    public const KEY_COASTER_ID = 'coaster_id';
    public const KEY_CAPACITY = 'ilosc_miejsc';
    public const KEY_SPEED = 'predkosc_wagonu';

    public static array $createRules = [
        self::KEY_CAPACITY => 'required|integer|greater_than[0]',
        self::KEY_SPEED => 'required|numeric|greater_than[0]',
    ];

    private string $id;

    public function __construct(
        string         $id = null,
        private string $coasterId = '',
        private int    $capacity = 0,
        private float  $speed = 0.0 // m/s
    ) {
        $this->id = $id ?? uniqid('wagon_');
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCoasterId(): string
    {
        return $this->coasterId;
    }

    public function setCoasterId(string $coasterId): self
    {
        $this->coasterId = $coasterId;

        return $this;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getSpeed(): float
    {
        return $this->speed;
    }

    public function setSpeed(float $speed): self
    {
        $this->speed = $speed;

        return $this;
    }

    public function toArray(): array
    {
        return [
            self::KEY_ID => $this->id,
            self::KEY_COASTER_ID => $this->coasterId,
            self::KEY_CAPACITY => $this->capacity,
            self::KEY_SPEED => $this->speed,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data[self::KEY_ID] ?? null,
            $data[self::KEY_COASTER_ID] ?? '',
            $data[self::KEY_CAPACITY] ?? 0,
            $data[self::KEY_SPEED] ?? 0.0
        );
    }

    public function getTimePerRideInMinutes(int $routeLength): float
    {
        $speedInMetersPerMinute = 60 * $this->speed;

        return $routeLength / $speedInMetersPerMinute;
    }
}
