<?php

namespace Tests\Unit\Repositories;

use App\Entities\Coaster;
use App\Repositories\CoasterRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Redis;

class CoasterRepositoryTest extends CIUnitTestCase
{
    use MockeryPHPUnitIntegration;

    protected Redis $redisMock;
    protected CoasterRepository $coasterRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->redisMock = Mockery::mock(Redis::class);
        $this->coasterRepository = new CoasterRepository($this->redisMock);

        $this->resetServices();
    }

    public function testSaveCoaster()
    {
        $coaster = new Coaster(
            'test_coaster',
            10,
            5000,
            32,
            1.2,
            1800,
            '08:00',
            '16:00'
        );

        $this->redisMock->shouldReceive('hMSet')
            ->once()
            ->with('coaster:test_coaster', $coaster->toArray())
            ->andReturn(true);

        $this->redisMock->shouldReceive('sAdd')
            ->once()
            ->with('coasters', 'test_coaster')
            ->andReturn(1);

        $result = $this->coasterRepository->save($coaster);

        $this->assertInstanceOf(Coaster::class, $result);
        $this->assertEquals('test_coaster', $result->getId());
    }

    public function testFindByIdExists()
    {
        $coasterId = 'test_coaster';
        $coasterData = [
            Coaster::KEY_ID => $coasterId,
            Coaster::KEY_STAFF => 10,
            Coaster::KEY_CLIENTS => 5000,
            Coaster::KEY_CAPACITY => 32,
            Coaster::KEY_SPEED => 1.2,
            Coaster::KEY_LENGTH => 1800,
            Coaster::KEY_HOUR_1 => '08:00',
            Coaster::KEY_HOUR_2 => '16:00',
        ];

        $this->redisMock->shouldReceive('exists')
            ->once()
            ->with("coaster:$coasterId")
            ->andReturn(true);

        $this->redisMock->shouldReceive('hGetAll')
            ->once()
            ->with("coaster:$coasterId")
            ->andReturn($coasterData);

        $result = $this->coasterRepository->findById($coasterId);

        $this->assertInstanceOf(Coaster::class, $result);
        $this->assertEquals($coasterId, $result->getId());
        $this->assertEquals(10, $result->getNumberOfStaff());
        $this->assertEquals(5000, $result->getNumberOfClients());
        $this->assertEquals(1800, $result->getRouteLength());
        $this->assertEquals('08:00', $result->getOpeningTime());
        $this->assertEquals('16:00', $result->getClosingTime());
    }

    public function testFindByIdNotExists()
    {
        $coasterId = 'non_existent_coaster';

        $this->redisMock->shouldReceive('exists')
            ->once()
            ->with("coaster:$coasterId")
            ->andReturn(false);

        $result = $this->coasterRepository->findById($coasterId);

        $this->assertNull($result);
    }

    public function testFindAll()
    {
        $coasterIds = ['coaster1', 'coaster2'];
        $coaster1Data = [
            Coaster::KEY_ID => 'coaster1',
            Coaster::KEY_STAFF => 10,
            Coaster::KEY_CLIENTS => 5000,
            Coaster::KEY_CAPACITY => 32,
            Coaster::KEY_SPEED => 1.2,
            Coaster::KEY_LENGTH => 1800,
            Coaster::KEY_HOUR_1 => '08:00',
            Coaster::KEY_HOUR_2 => '16:00',
        ];
        $coaster2Data = [
            Coaster::KEY_ID => 'coaster2',
            Coaster::KEY_STAFF => 15,
            Coaster::KEY_CLIENTS => 6000,
            Coaster::KEY_CAPACITY => 32,
            Coaster::KEY_SPEED => 1.2,
            Coaster::KEY_LENGTH => 2000,
            Coaster::KEY_HOUR_1 => '9:00',
            Coaster::KEY_HOUR_2 => '17:00',
        ];

        $this->redisMock->shouldReceive('sMembers')
            ->once()
            ->with('coasters')
            ->andReturn($coasterIds);

        $this->redisMock->shouldReceive('exists')
            ->once()
            ->with('coaster:coaster1')
            ->andReturn(true);

        $this->redisMock->shouldReceive('hGetAll')
            ->once()
            ->with('coaster:coaster1')
            ->andReturn($coaster1Data);

        $this->redisMock->shouldReceive('exists')
            ->once()
            ->with('coaster:coaster2')
            ->andReturn(true);

        $this->redisMock->shouldReceive('hGetAll')
            ->once()
            ->with('coaster:coaster2')
            ->andReturn($coaster2Data);

        $results = $this->coasterRepository->findAll();

        $this->assertCount(2, $results);
        $this->assertInstanceOf(Coaster::class, $results[0]);
        $this->assertInstanceOf(Coaster::class, $results[1]);
        $this->assertEquals('coaster1', $results[0]->getId());
        $this->assertEquals('coaster2', $results[1]->getId());
    }

    public function testExists()
    {
        $coasterId = 'test_coaster';

        $this->redisMock->shouldReceive('sIsMember')
            ->once()
            ->with('coasters', $coasterId)
            ->andReturn(true);

        $result = $this->coasterRepository->exists($coasterId);

        $this->assertTrue($result);
    }
}
