<?php

namespace App\Entities;

class Coaster
{
    public const KEY_ID = 'id';
    public const KEY_STAFF = 'liczba_personelu';
    public const KEY_CLIENTS = 'liczba_klientow';
    public const KEY_LENGTH = 'dl_trasy';
    public const KEY_HOUR_1 = 'godziny_od';
    public const KEY_HOUR_2 = 'godziny_do';

    public static array $createRules = [
        self::KEY_STAFF => 'required|integer|greater_than[0]',
        self::KEY_CLIENTS => 'required|integer|greater_than[0]',
        self::KEY_LENGTH => 'required|integer|greater_than[0]',
        self::KEY_HOUR_1 => 'required|valid_time',
        self::KEY_HOUR_2 => 'required|valid_time',
    ];

    public static array $updateRules = [
        self::KEY_STAFF => 'required|integer|greater_than[0]',
        self::KEY_CLIENTS => 'required|integer|greater_than[0]',
        self::KEY_HOUR_1 => 'required|valid_time',
        self::KEY_HOUR_2 => 'required|valid_time',
    ];

    private string $id;

    public function __construct(
        string         $id = null,
        private int    $numberOfStaff = 0,
        private int    $numberOfClients = 0,
        private int    $routeLength = 0,
        private string $openingTime = '',
        private string $closingTime = ''
    ) {
        $this->id = $id ?? uniqid('coaster_');
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNumberOfStaff(): int
    {
        return $this->numberOfStaff;
    }

    public function setNumberOfStaff(int $numberOfStaff): self
    {
        $this->numberOfStaff = $numberOfStaff;

        return $this;
    }

    public function getNumberOfClients(): int
    {
        return $this->numberOfClients;
    }

    public function setNumberOfClients(int $numberOfClients): self
    {
        $this->numberOfClients = $numberOfClients;

        return $this;
    }

    /** in meters */
    public function getRouteLength(): int
    {
        return $this->routeLength;
    }

    public function setRouteLength(int $routeLength): self
    {
        $this->routeLength = $routeLength;

        return $this;
    }

    public function getOpeningTime(): string
    {
        return $this->openingTime;
    }

    public function setOpeningTime(string $openingTime): self
    {
        $this->openingTime = $openingTime;

        return $this;
    }

    public function getClosingTime(): string
    {
        return $this->closingTime;
    }

    public function setClosingTime(string $closingTime): self
    {
        $this->closingTime = $closingTime;

        return $this;
    }

    public function toArray(): array
    {
        return [
            self::KEY_ID => $this->id,
            self::KEY_STAFF => $this->numberOfStaff,
            self::KEY_CLIENTS => $this->numberOfClients,
            self::KEY_LENGTH => $this->routeLength,
            self::KEY_HOUR_1 => $this->openingTime,
            self::KEY_HOUR_2 => $this->closingTime,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data[self::KEY_ID] ?? null,
            $data[self::KEY_STAFF] ?? 0,
            $data[self::KEY_CLIENTS] ?? 0,
            $data[self::KEY_LENGTH] ?? 0,
            $data[self::KEY_HOUR_1] ?? '',
            $data[self::KEY_HOUR_2] ?? ''
        );
    }

    public function getWorkingTimeInMinutes(): int
    {
        $timeFrom = strtotime($this->openingTime);
        $timeTo = strtotime($this->closingTime);

        if ($timeTo < $timeFrom) {
            $timeTo += 24 * 60 * 60;
        }

        return ($timeTo - $timeFrom) / 60;
    }
}
