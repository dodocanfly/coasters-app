<?php

namespace Tests\Feature;

use App\Entities\Coaster;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Redis;

class ApiCoasterTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private ?Redis $redis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->redis = service('redisClient');
        $this->redis->flushDB();
        $this->resetServices();
    }

    public function testUpdateCoaster()
    {
        $payload = [
            Coaster::KEY_STAFF => 16,
            Coaster::KEY_CLIENTS => 66666,
            Coaster::KEY_CAPACITY => 32,
            Coaster::KEY_SPEED => 1.2,
            Coaster::KEY_LENGTH => 1800,
            Coaster::KEY_HOUR_1 => '08:00',
            Coaster::KEY_HOUR_2 => '16:00',
        ];

        $createResult = $this->withBodyFormat('json')->post('api/coasters', $payload);
        $responseBody = json_decode($createResult->getJSON(), true);
        $coasterId = $responseBody['data'][Coaster::KEY_ID];

        $updatePayload = [
            Coaster::KEY_STAFF => 20,
            Coaster::KEY_CLIENTS => 70000,
            Coaster::KEY_CAPACITY => 32,
            Coaster::KEY_SPEED => 1.2,
            Coaster::KEY_HOUR_1 => '09:00',
            Coaster::KEY_HOUR_2 => '17:00'
        ];

        $updateResult = $this->withBodyFormat('json')->put("api/coasters/{$coasterId}", $updatePayload);

        $updateResult->assertStatus(200);
        $updateResult->assertJSONFragment(['success' => true]);
        $updateResult->assertJSONFragment(['message' => 'Coaster updated successfully']);

        $updatedCoaster = $this->redis->hGetAll("coaster:{$coasterId}");

        $this->assertEquals(20, $updatedCoaster[Coaster::KEY_STAFF]);
        $this->assertEquals(70000, $updatedCoaster[Coaster::KEY_CLIENTS]);
        $this->assertEquals(1800, $updatedCoaster[Coaster::KEY_LENGTH]);
        $this->assertEquals('09:00', $updatedCoaster[Coaster::KEY_HOUR_1]);
        $this->assertEquals('17:00', $updatedCoaster[Coaster::KEY_HOUR_2]);
    }

    public function testCreateCoaster()
    {
        $payload = [
            Coaster::KEY_STAFF => 16,
            Coaster::KEY_CLIENTS => 60000,
            Coaster::KEY_CAPACITY => 32,
            Coaster::KEY_SPEED => 1.2,
            Coaster::KEY_LENGTH => 1800,
            Coaster::KEY_HOUR_1 => '08:00',
            Coaster::KEY_HOUR_2 => '16:00'
        ];

        $result = $this->withBodyFormat('json')->post('api/coasters', $payload);

        $result->assertStatus(201);
        $result->assertJSONFragment(['success' => true]);
        $result->assertJSONFragment(['message' => 'Coaster created successfully']);

        $responseBody = json_decode($result->getJSON(), true);
        $coasterId = $responseBody['data'][Coaster::KEY_ID];

        $this->assertTrue((bool)$this->redis->exists("coaster:{$coasterId}"));

        $savedCoaster = $this->redis->hGetAll("coaster:{$coasterId}");
        $this->assertEquals(16, $savedCoaster[Coaster::KEY_STAFF]);
        $this->assertEquals(60000, $savedCoaster[Coaster::KEY_CLIENTS]);
        $this->assertEquals(1800, $savedCoaster[Coaster::KEY_LENGTH]);
        $this->assertEquals('08:00', $savedCoaster[Coaster::KEY_HOUR_1]);
        $this->assertEquals('16:00', $savedCoaster[Coaster::KEY_HOUR_2]);
    }

    public function testCreateCoasterValidationFail()
    {
        $payload = [
            Coaster::KEY_STAFF => 16,
            Coaster::KEY_LENGTH => 1800,
            Coaster::KEY_HOUR_2 => '16:16'
        ];

        $result = $this->withBodyFormat('json')->post('api/coasters', $payload);

        $result->assertStatus(400);
    }

    public function testUpdateNonExistentCoaster()
    {
        $nonExistentId = 'non_existent_coaster';

        $updatePayload = [
            Coaster::KEY_STAFF => 20,
            Coaster::KEY_CLIENTS => 70000,
            Coaster::KEY_CAPACITY => 32,
            Coaster::KEY_SPEED => 1.2,
            Coaster::KEY_HOUR_1 => '09:00',
            Coaster::KEY_HOUR_2 => '17:00'
        ];

        $result = $this->withBodyFormat('json')->put("api/coasters/{$nonExistentId}", $updatePayload);

        $result->assertStatus(404);
    }
}
