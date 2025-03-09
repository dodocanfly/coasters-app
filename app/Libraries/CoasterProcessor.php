<?php

namespace App\Libraries;

use App\Entities\Coaster;
use App\Entities\Wagon;

class CoasterProcessor
{
    /** @var int in minutes */
    private const BREAK_TIME = 5;
    private const STAFF_PER_COASTER = 1;
    private const STAFF_PER_WAGON = 2;
    private const DEFAULT_WAGON_CAPACITY = 20;
    private const DEFAULT_WAGON_SPEED = 1;

    private int $calculatedCapacity;
    private int $numberOfWagons;
    private int $numberOfRidesPerDay;
    private array $wagonSpeedList = [];
    private array $wagonCapacityList = [];
    private array $statusList = [];

    /** @param Wagon[] $wagons */
    public function __construct(
        private readonly Coaster $coaster,
        private readonly array $wagons
    ) {
        $this->processWagons();
        $this->processCoaster();
    }

    public function getHeader(): string
    {
        return sprintf(
            '[ %s - Kolejka %s - dł. %dm, pr. %.1fm/s ]',
            date('H:i'),
            $this->coaster->getId(),
            $this->coaster->getRouteLength(),
            $this->getLowestWagonSpeed()
        );
    }

    public function getCliReport(): string
    {
        $data = [
            '{nl}' => PHP_EOL,
            '{openingTime}' => $this->coaster->getOpeningTime(),
            '{closingTime}' => $this->coaster->getClosingTime(),
            '{availableWagons}' => $this->getAvailableWagons(),
            '{requiredWagons}' => $this->getRequiredWagons(),
            '{lowestWagonSpeed}' => $this->getLowestWagonSpeed(),
            '{numberOfRidesPerDay}' => $this->getNumberOfRidesPerDay(),
            '{averageWagonCapacity}' => $this->getAverageWagonCapacity(),
            '{totalWagonsCapacity}' => $this->getTotalWagonsCapacity(),
            '{totalDailyCapacity}' => $this->getCalculatedCoasterCapacity(),
            '{availableStaff}' => $this->getAvailableStaff(),
            '{requiredStaff}' => $this->getRequiredStaff(),
            '{numberOfWagonsPossibleToHandle}' => $this->getNumberOfWagonsPossibleToHandle(),
            '{numberOfClients}' => $this->coaster->getNumberOfClients(),
            '{status}' => $this->getStatus(),
        ];

        $template = '1. Godziny działania: {openingTime} - {closingTime}{nl}' .
            '2. Liczba wagonów: {availableWagons} dostępnych / {requiredWagons} wymaganych (średniej pojemności){nl}' .
            '   - prędkość najwolniejszego wagonu: {lowestWagonSpeed} (m/s){nl}' .
            '   - maksymalna liczba przejazdów: {numberOfRidesPerDay}{nl}' .
            '   - średnia pojemność wagonu: {averageWagonCapacity}{nl}' .
            '   - łączna pojemność wszystkich wagonów: {totalWagonsCapacity}{nl}' .
            '   - maksymalna dzienna przepustowość: {totalDailyCapacity}{nl}' .
            '3. Liczba personelu: {availableStaff} dostępnych / {requiredStaff} wymaganych{nl}' .
            '   - możliwe do obsłużenia wagony: {numberOfWagonsPossibleToHandle}{nl}' .
            '4. Spodziewana liczba klientów: {numberOfClients}{nl}' .
            '5. STATUS: {status}{nl}' .
            '--------------------------------------------------------------------------------';

        return strtr($template, $data);
    }

    public function getStatus(): string
    {
        if (empty($this->statusList)) {
            return 'OK';
        }

        return 'PROBLEM - ' . implode(', ', $this->statusList);
    }

    public function getLogReport(): string
    {
        return sprintf(
            'Kolejka %s, godz. %s - %s, dł. %dm, personel: %d / %d, klienci: %d, wagony: %d / %d, PROBLEM: %s',
            $this->coaster->getId(),
            $this->coaster->getOpeningTime(),
            $this->coaster->getClosingTime(),
            $this->coaster->getRouteLength(),
            $this->getAvailableStaff(),
            $this->getRequiredStaff(),
            $this->coaster->getNumberOfClients(),
            $this->getAvailableWagons(),
            $this->getRequiredWagons(),
            implode(', ', $this->statusList)
        );
    }

    public function isError(): bool
    {
        return !empty($this->statusList);
    }

    private function processWagons(): void
    {
        $this->wagonSpeedList = [];
        $this->wagonCapacityList = [];
        $this->numberOfWagons = count($this->wagons);

        foreach ($this->wagons as $wagon) {
            $this->wagonSpeedList[] = $wagon->getSpeed();
            $this->wagonCapacityList[] = $wagon->getCapacity();
        }
    }

    private function processCoaster(): void
    {
        if ($this->notEnoughWagons()) {
            $this->statusList[] = 'brakuje ' . $this->getWagonsDifference() . ' wagonów';
        } elseif ($this->tooHighCoasterCapacity()) {
            $this->statusList[] = 'nadmiar ' . $this->getWagonsDifference() . ' wagonów';
        }

        if ($this->notEnoughStaff()) {
            $this->statusList[] = 'brakuje ' . $this->getStaffDifference() . ' pracowników';
        } elseif ($this->tooMuchStaff()) {
            $this->statusList[] = 'nadmiar ' . $this->getStaffDifference() . ' pracowników';
        }

    }

    private function notEnoughWagons(): bool
    {
        return $this->coaster->getNumberOfClients() > $this->getCalculatedCoasterCapacity();
    }

    private function tooHighCoasterCapacity(): bool
    {
        return $this->getCalculatedCoasterCapacity() > ($this->coaster->getNumberOfClients() * 2)
            && !$this->notEnoughStaff();
    }

    private function getWagonsDifference(): int
    {
        return abs($this->getAvailableWagons() - $this->getRequiredWagons());
    }

    private function notEnoughStaff(): bool
    {
        return $this->getAvailableStaff() < $this->getRequiredStaff();
    }

    private function tooMuchStaff(): bool
    {
        return $this->getAvailableStaff() > $this->getRequiredStaff();
    }

    private function getStaffDifference(): int
    {
        return abs($this->getAvailableStaff() - $this->getRequiredStaff());
    }

    private function getAvailableWagons(): int
    {
        return $this->numberOfWagons;
    }

    private function getRequiredWagons(): int
    {
        return ceil($this->coaster->getNumberOfClients() / ($this->getNumberOfRidesPerDay() * $this->getAverageWagonCapacity()));
    }

    private function getAvailableStaff(): int
    {
        return $this->coaster->getNumberOfStaff();
    }

    private function getRequiredStaff(): int
    {
        $totalWagons = $this->numberOfWagons;
        if ($this->notEnoughWagons()) {
            $totalWagons += $this->getWagonsDifference();
        }

        return self::STAFF_PER_COASTER + $totalWagons * self::STAFF_PER_WAGON;
    }

    private function getCalculatedCoasterCapacity(): int
    {
        if (!isset($this->calculatedCapacity)) {
            $this->calculatedCapacity = $this->getNumberOfRidesPerDay() * $this->getTotalWagonsCapacity();
        }

        return $this->calculatedCapacity;
    }

    private function getNumberOfRidesPerDay(): int
    {
        if (!isset($this->numberOfRidesPerDay)) {
            $speedInMetersPerMinute = 60 * $this->getLowestWagonSpeed();
            $approxRideTimeInMinutes = $this->coaster->getRouteLength() / $speedInMetersPerMinute;
            $approxTotalCycleTimeInMinutes = $approxRideTimeInMinutes + self::BREAK_TIME;
            $this->numberOfRidesPerDay = floor($this->coaster->getWorkingTimeInMinutes() / $approxTotalCycleTimeInMinutes);
        }

        return $this->numberOfRidesPerDay;
    }

    private function getAverageWagonCapacity(): float
    {
        if (getenv('CAPACITY_FROM_COASTER')) {
            return $this->coaster->getWagonCapacity();
        }

        if ($this->numberOfWagons === 0) {
            return self::DEFAULT_WAGON_CAPACITY;
        }

        return round($this->getTotalWagonsCapacity() / $this->numberOfWagons);
    }

    private function getLowestWagonSpeed(): float
    {
        if (getenv('SPEED_FROM_COASTER')) {
            return $this->coaster->getWagonSpeed();
        }

        if ($this->numberOfWagons === 0) {
            return self::DEFAULT_WAGON_SPEED;
        }

        return min($this->wagonSpeedList);
    }

    private function getTotalWagonsCapacity(): int
    {
        return getenv('CAPACITY_FROM_COASTER') ?
            $this->coaster->getWagonCapacity() * $this->numberOfWagons
            : array_sum($this->wagonCapacityList);
    }

    private function getNumberOfWagonsPossibleToHandle(): int
    {
        return floor(($this->coaster->getNumberOfStaff() - self::STAFF_PER_COASTER) / self::STAFF_PER_WAGON);
    }
}
