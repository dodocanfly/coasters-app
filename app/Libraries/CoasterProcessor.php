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

    /** in meters per second */
    private float $lowestWagonSpeed = 0;
    private int $calculatedDailyCoasterCapacity = 0;
    private int $numberOfWagons;
    private int $averageWagonCapacity = 0;
    private int $totalWagonsCapacity = 0;
    private int $numberOfRidesPerDay = 0;
    private array $statusList = [];

    /** @param Wagon[] $wagons */
    private function __construct(
        private readonly Coaster $coaster,
        private readonly array $wagons
    ) {
    }

    /** @param Wagon[] $wagons */
    public static function build(Coaster $coaster, array $wagons): self
    {
        $processor = new self($coaster, $wagons);

        $processor->processWagons();
        $processor->processCoaster();

        return $processor;
    }

    public function getHeader(): string
    {
        return '[ Kolejka ' . $this->coaster->getId() . ' - dł. ' . $this->coaster->getRouteLength() . 'm ]';
    }

    public function getCliReport(): string
    {
        $txt = '1. Godziny działania: ' . $this->coaster->getOpeningTime() . ' - ' . $this->coaster->getClosingTime() . "\n";
        $txt .= '2. Liczba wagonów: ' . $this->getAvailableWagons() . ' dostępnych / ' . $this->getRequiredWagons() . ' wymaganych (średniej pojemności)' . "\n";
        $txt .= '   - prędkość najwolniejszego wagonu: ' . $this->lowestWagonSpeed . ' (m/s)' . "\n";
        $txt .= '   - maksymalna liczba przejazdów: ' . $this->numberOfRidesPerDay . "\n";
        $txt .= '   - średnia pojemność wagonu: ' . $this->averageWagonCapacity . "\n";
        $txt .= '   - łączna pojemność wszystkich wagonów: ' . $this->totalWagonsCapacity . "\n";
        $txt .= '   - maksymalna dzienna przepustowość: ' . $this->getTotalDailyCapacity() . "\n";
        $txt .= '3. Liczba personelu: ' . $this->getAvailableStaff() . ' dostępnych / ' . $this->getRequiredStaff() . ' wymaganych' . "\n";
        $txt .= '   - możliwe do obsłużenia wagony: ' . $this->getNumberOfWagonsPossibleToHandle() . "\n";
        $txt .= '4. Spodziewana liczba klientów: ' . $this->coaster->getNumberOfClients() . "\n";

        if (empty($this->statusList)) {
            $txt .= '5. STATUS: OK' . "\n";
        } else {
            $txt .= '5. STATUS: PROBLEM - ' . implode(', ', $this->statusList) . "\n";
        }

        $txt .= str_pad('', 80, '-');

        return $txt;
    }

    public function getLogReport(): string
    {
        return 'Kolejka ' . $this->coaster->getId() . ', ' .
            'godz. ' . $this->coaster->getOpeningTime() . '-' . $this->coaster->getClosingTime() . ', ' .
            'dł. ' . $this->coaster->getRouteLength() . 'm, ' .
            'personel: ' . $this->getAvailableStaff() . ' / ' . $this->getRequiredStaff() . ', ' .
            'klienci: ' . $this->coaster->getNumberOfClients() . ', ' .
            'wagony: ' . $this->getAvailableWagons() . ' / ' . $this->getRequiredWagons() . ', ' .
            'PROBLEM: ' . implode(', ', $this->statusList);
    }

    public function isError(): bool
    {
        return !empty($this->statusList);
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

    private function processWagons(): void
    {
        $wagonSpeedList = [];
        $wagonCapacityList = [];
        $this->numberOfWagons = count($this->wagons);

        foreach ($this->wagons as $wagon) {
            $wagonSpeedList[] = $wagon->getSpeed();
            $wagonCapacityList[] = $wagon->getCapacity();
        }

        $this->totalWagonsCapacity = array_sum($wagonCapacityList);

        if ($this->numberOfWagons === 0) {
            $this->averageWagonCapacity = self::DEFAULT_WAGON_CAPACITY;
            $this->lowestWagonSpeed = self::DEFAULT_WAGON_SPEED;
        } else {
            $this->averageWagonCapacity = round($this->totalWagonsCapacity / $this->numberOfWagons);
            $this->lowestWagonSpeed = min($wagonSpeedList);
        }

        $speedInMetersPerMinute = 60 * $this->lowestWagonSpeed;
        $approxRideTimeInMinutes = $this->coaster->getRouteLength() / $speedInMetersPerMinute;
        $approxTotalCycleTimeInMinutes = $approxRideTimeInMinutes + self::BREAK_TIME;
        $this->numberOfRidesPerDay = floor($this->coaster->getWorkingTimeInMinutes() / $approxTotalCycleTimeInMinutes);

        $this->calculatedDailyCoasterCapacity = $this->numberOfRidesPerDay * $this->totalWagonsCapacity;
    }

    private function getAvailableWagons(): int
    {
        return $this->numberOfWagons;
    }

    private function getRequiredWagons(): int
    {
        return ceil($this->coaster->getNumberOfClients() / ($this->numberOfRidesPerDay * $this->averageWagonCapacity));
    }

    private function getWagonsDifference(): int
    {
        return abs($this->getAvailableWagons() - $this->getRequiredWagons());
    }

    private function notEnoughWagons(): bool
    {
        return $this->coaster->getNumberOfClients() > $this->calculatedDailyCoasterCapacity;
    }

    private function tooHighCoasterCapacity(): bool
    {
        return $this->calculatedDailyCoasterCapacity > ($this->coaster->getNumberOfClients() * 2)
            && !$this->notEnoughStaff();
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

    private function getStaffDifference(): int
    {
        return abs($this->getAvailableStaff() - $this->getRequiredStaff());
    }

    private function notEnoughStaff(): bool
    {
        return $this->getAvailableStaff() < $this->getRequiredStaff();
    }

    private function tooMuchStaff(): bool
    {
        return $this->getAvailableStaff() > $this->getRequiredStaff();
    }

    private function getTotalDailyCapacity(): int
    {
        return $this->calculatedDailyCoasterCapacity;
    }

    private function getNumberOfWagonsPossibleToHandle(): int
    {
        return floor(($this->coaster->getNumberOfStaff() - self::STAFF_PER_COASTER) / self::STAFF_PER_WAGON);
    }
}
