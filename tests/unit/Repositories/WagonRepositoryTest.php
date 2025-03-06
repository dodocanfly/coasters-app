<?php

namespace Tests\Unit\Repositories;

use App\Entities\Wagon;
use App\Repositories\WagonRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Redis;

class WagonRepositoryTest extends CIUnitTestCase
{
    use MockeryPHPUnitIntegration;

    protected Redis $redisMock;
    protected WagonRepository $wagonRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->redisMock = Mockery::mock(Redis::class);
        $this->wagonRepository = new WagonRepository($this->redisMock);

        $this->resetServices();
    }

    public function testSaveWagon()
    {
        $wagon = new Wagon(
            'test_wagon',
            'test_coaster',
            32,
            1.2
        );

        $this->redisMock->shouldReceive('hMSet')
            ->once()
            ->with('wagon:test_wagon', $wagon->toArray())
            ->andReturn(true);

        $this->redisMock->shouldReceive('sAdd')
            ->once()
            ->with('coaster:test_coaster:wagons', 'test_wagon')
            ->andReturn(1);

        $this->redisMock->shouldReceive('sAdd')
            ->once()
            ->with('wagons', 'test_wagon')
            ->andReturn(1);

        $result = $this->wagonRepository->save($wagon);

        $this->assertInstanceOf(Wagon::class, $result);
        $this->assertEquals('test_wagon', $result->getId());
    }

    public function testFindByIdExists()
    {
        $wagonId = 'test_wagon';
        $wagonData = [
            Wagon::KEY_ID => $wagonId,
            Wagon::KEY_COASTER_ID => 'test_coaster',
            Wagon::KEY_CAPACITY => 32,
            Wagon::KEY_SPEED => 1.2,
        ];

        $this->redisMock->shouldReceive('exists')
            ->once()
            ->with("wagon:$wagonId")
            ->andReturn(true);

        $this->redisMock->shouldReceive('hGetAll')
            ->once()
            ->with("wagon:$wagonId")
            ->andReturn($wagonData);

        $result = $this->wagonRepository->findById($wagonId);

        $this->assertInstanceOf(Wagon::class, $result);
        $this->assertEquals($wagonId, $result->getId());
        $this->assertEquals('test_coaster', $result->getCoasterId());
        $this->assertEquals(32, $result->getCapacity());
        $this->assertEquals(1.2, $result->getSpeed());
    }

    public function testFindByCoasterId()
    {
        $coasterId = 'test_coaster';
        $wagonIds = ['wagon1', 'wagon2'];
        $wagon1Data = [
            Wagon::KEY_ID => 'wagon1',
            Wagon::KEY_COASTER_ID => $coasterId,
            Wagon::KEY_CAPACITY => 32,
            Wagon::KEY_SPEED => 1.2,
        ];
        $wagon2Data = [
            Wagon::KEY_ID => 'wagon2',
            Wagon::KEY_COASTER_ID => $coasterId,
            Wagon::KEY_CAPACITY => 40,
            Wagon::KEY_SPEED => 1.5,
        ];

        $this->redisMock->shouldReceive('sMembers')
            ->once()
            ->with("coaster:$coasterId:wagons")
            ->andReturn($wagonIds);

        $this->redisMock->shouldReceive('exists')
            ->once()
            ->with('wagon:wagon1')
            ->andReturn(true);

        $this->redisMock->shouldReceive('hGetAll')
            ->once()
            ->with('wagon:wagon1')
            ->andReturn($wagon1Data);

        $this->redisMock->shouldReceive('exists')
            ->once()
            ->with('wagon:wagon2')
            ->andReturn(true);

        $this->redisMock->shouldReceive('hGetAll')
            ->once()
            ->with('wagon:wagon2')
            ->andReturn($wagon2Data);

        $results = $this->wagonRepository->findByCoasterId($coasterId);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(Wagon::class, $results[0]);
        $this->assertInstanceOf(Wagon::class, $results[1]);
        $this->assertEquals('wagon1', $results[0]->getId());
        $this->assertEquals('wagon2', $results[1]->getId());
    }

    public function testDelete()
    {
        $wagonId = 'test_wagon';
        $coasterId = 'test_coaster';
        $wagonData = [
            Wagon::KEY_ID => $wagonId,
            Wagon::KEY_COASTER_ID => $coasterId,
            Wagon::KEY_CAPACITY => 32,
            Wagon::KEY_SPEED => 1.2,
        ];

        $this->redisMock->shouldReceive('exists')
            ->once()
            ->with("wagon:$wagonId")
            ->andReturn(true);

        $this->redisMock->shouldReceive('hGetAll')
            ->once()
            ->with("wagon:$wagonId")
            ->andReturn($wagonData);

        $this->redisMock->shouldReceive('sRem')
            ->once()
            ->with("coaster:$coasterId:wagons", $wagonId)
            ->andReturn(1);

        $this->redisMock->shouldReceive('sRem')
            ->once()
            ->with('wagons', $wagonId)
            ->andReturn(1);

        $this->redisMock->shouldReceive('del')
            ->once()
            ->with("wagon:$wagonId")
            ->andReturn(1);

        $result = $this->wagonRepository->delete($wagonId);

        $this->assertTrue($result);
    }

    public function testBelongsToCoaster()
    {
        $wagonId = 'test_wagon';
        $coasterId = 'test_coaster';

        $this->redisMock->shouldReceive('sIsMember')
            ->once()
            ->with("coaster:$coasterId:wagons", $wagonId)
            ->andReturn(true);

        $result = $this->wagonRepository->belongsToCoaster($wagonId, $coasterId);

        $this->assertTrue($result);
    }

    public function testCountWagonsByCoasterId()
    {
        $coasterId = 'test_coaster';

        $this->redisMock->shouldReceive('sCard')
            ->once()
            ->with("coaster:$coasterId:wagons")
            ->andReturn(5);

        $result = $this->wagonRepository->countWagonsByCoasterId($coasterId);

        $this->assertEquals(5, $result);
    }
}
