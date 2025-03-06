<?php

namespace Tests\Feature;

use App\Entities\Coaster;
use App\Entities\Wagon;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Redis;

class ApiWagonTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private string $coasterId;
    private ?Redis $redis;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $this->redis = service('redisClient');
        $this->redis->flushDB();

        $payload = [
            Coaster::KEY_STAFF => 16,
            Coaster::KEY_CLIENTS => 60000,
            Coaster::KEY_LENGTH => 1800,
            Coaster::KEY_HOUR_1 => '08:00',
            Coaster::KEY_HOUR_2 => '16:00'
        ];

        $result = $this->withBodyFormat('json')->post('api/coasters', $payload);
        $responseBody = json_decode($result->getJSON(), true);

        $this->coasterId = $responseBody['data'][Coaster::KEY_ID];
    }

    public function testAddWagon()
    {
        $payload = [
            Wagon::KEY_CAPACITY => 32,
            Wagon::KEY_SPEED => 1.2
        ];

        $result = $this->withBodyFormat('json')->post("api/coasters/$this->coasterId/wagons", $payload);

        $result->assertStatus(201);
        $result->assertJSONFragment(['success' => true]);
        $result->assertJSONFragment(['message' => 'Wagon added successfully']);

        $responseBody = json_decode($result->getJSON(), true);
        $wagonId = $responseBody['data'][Wagon::KEY_ID];

        $this->assertTrue((bool)$this->redis->exists("wagon:$wagonId"));

        $savedWagon = $this->redis->hGetAll("wagon:$wagonId");
        $this->assertEquals($this->coasterId, $savedWagon[Wagon::KEY_COASTER_ID]);
        $this->assertEquals(32, $savedWagon[Wagon::KEY_CAPACITY]);
        $this->assertEquals(1.2, $savedWagon[Wagon::KEY_SPEED]);

        $this->assertTrue($this->redis->sIsMember("coaster:$this->coasterId:wagons", $wagonId));
    }

    public function testAddWagonToNonExistentCoaster()
    {
        $nonExistentId = 'non_existent_coaster';

        $payload = [
            Wagon::KEY_CAPACITY => 32,
            Wagon::KEY_SPEED => 1.2
        ];

        $result = $this->withBodyFormat('json')->post("api/coasters/$nonExistentId/wagons", $payload);

        $result->assertStatus(404);
    }

    public function testRemoveWagon()
    {
        $payload = [
            Wagon::KEY_CAPACITY => 32,
            Wagon::KEY_SPEED => 1.2
        ];

        $addResult = $this->withBodyFormat('json')->post("api/coasters/$this->coasterId/wagons", $payload);
        $responseBody = json_decode($addResult->getJSON(), true);
        $wagonId = $responseBody['data'][Wagon::KEY_ID];

        $removeResult = $this->delete("api/coasters/$this->coasterId/wagons/$wagonId");

        $removeResult->assertStatus(200);
        $removeResult->assertJSONFragment(['success' => true]);
        $removeResult->assertJSONFragment(['message' => 'Wagon removed successfully']);

        $this->assertFalse((bool)$this->redis->exists("wagon:$wagonId"));
        $this->assertFalse($this->redis->sIsMember("coaster:$this->coasterId:wagons", $wagonId));
    }

    public function testRemoveNonExistentWagon()
    {
        $nonExistentWagonId = 'non_existent_wagon';

        $result = $this->delete("api/coasters/$this->coasterId/wagons/$nonExistentWagonId");

        $result->assertStatus(404);
    }
}
