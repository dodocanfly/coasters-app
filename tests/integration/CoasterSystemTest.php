<?php

namespace Tests\Integration;

use App\Entities\Coaster;
use App\Entities\Wagon;
use App\Repositories\CoasterRepository;
use App\Repositories\WagonRepository;
use App\Services\CoasterService;
use App\Services\MonitoringService;
use CodeIgniter\Test\CIUnitTestCase;
use Redis;

class CoasterSystemTest extends CIUnitTestCase
{
    protected ?CoasterRepository $coasterRepository;
    protected ?WagonRepository $wagonRepository;
    protected ?CoasterService $coasterService;
    protected ?MonitoringService $monitoringService;
    protected ?Redis $redis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coasterRepository = service('coasterRepository');
        $this->wagonRepository = service('wagonRepository');
        $this->coasterService = service('coasterService');
        $this->monitoringService = service('monitoringService');
        $this->redis = service('redisClient');
        $this->redis->flushDB();
        $this->resetServices();
    }

    public function testCreateAndRetrieveCoaster()
    {
        $coaster = new Coaster(
            null,
            21,
            600,
            300,
            '09:00',
            '10:00'
        );

        $savedCoaster = $this->coasterRepository->save($coaster);
        $coasterId = $savedCoaster->getId();

        $retrievedCoaster = $this->coasterRepository->findById($coasterId);

        $this->assertNotNull($retrievedCoaster);
        $this->assertEquals($coasterId, $retrievedCoaster->getId());
        $this->assertEquals(21, $retrievedCoaster->getNumberOfStaff());
        $this->assertEquals(600, $retrievedCoaster->getNumberOfClients());
        $this->assertEquals(300, $retrievedCoaster->getRouteLength());
        $this->assertEquals('09:00', $retrievedCoaster->getOpeningTime());
        $this->assertEquals('10:00', $retrievedCoaster->getClosingTime());
    }

    public function testAddAndRemoveWagons()
    {
        $coaster = new Coaster(
            null,
            15,
            5000,
            1800,
            '09:00',
            '17:00'
        );

        $savedCoaster = $this->coasterRepository->save($coaster);
        $coasterId = $savedCoaster->getId();

        $wagon1 = new Wagon(
            null,
            $coasterId,
            32,
            1.2
        );

        $savedWagon1 = $this->wagonRepository->save($wagon1);
        $wagon1Id = $savedWagon1->getId();

        $wagon2 = new Wagon(
            null,
            $coasterId,
            40,
            1.5
        );

        $savedWagon2 = $this->wagonRepository->save($wagon2);
        $wagon2Id = $savedWagon2->getId();

        $coasterWagons = $this->wagonRepository->findByCoasterId($coasterId);

        $this->assertCount(2, $coasterWagons);

        $foundWagon1 = false;
        $foundWagon2 = false;

        foreach ($coasterWagons as $wagon) {
            if ($wagon->getId() === $wagon1Id) {
                $foundWagon1 = true;
                $this->assertEquals(32, $wagon->getCapacity());
                $this->assertEquals(1.2, $wagon->getSpeed());
            } elseif ($wagon->getId() === $wagon2Id) {
                $foundWagon2 = true;
                $this->assertEquals(40, $wagon->getCapacity());
                $this->assertEquals(1.5, $wagon->getSpeed());
            }
        }

        $this->assertTrue($foundWagon1, 'First wagon not found');
        $this->assertTrue($foundWagon2, 'Second wagon not found');

        $deleteResult = $this->wagonRepository->delete($wagon1Id);
        $this->assertTrue($deleteResult);

        $updatedCoasterWagons = $this->wagonRepository->findByCoasterId($coasterId);
        $this->assertCount(1, $updatedCoasterWagons);
        $this->assertEquals($wagon2Id, $updatedCoasterWagons[0]->getId());
    }
}
