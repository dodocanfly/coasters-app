<?php

namespace Tests\Unit\Validation;

use App\Entities\Coaster;
use App\Validation\DateTimeRules;
use CodeIgniter\Test\CIUnitTestCase;

class CoasterRulesTest extends CIUnitTestCase
{
    public function testValidTimeSuccess()
    {
        $this->assertTrue(DateTimeRules::valid_time('00:00'));
        $this->assertTrue(DateTimeRules::valid_time('12:30'));
        $this->assertTrue(DateTimeRules::valid_time('23:59'));
    }

    public function testValidTimeFail()
    {
        $this->assertFalse(DateTimeRules::valid_time('24:00'));
        $this->assertFalse(DateTimeRules::valid_time('12:60'));
        $this->assertFalse(DateTimeRules::valid_time('12.30'));
        $this->assertFalse(DateTimeRules::valid_time('1230'));
        $this->assertFalse(DateTimeRules::valid_time('12:3'));
        $this->assertFalse(DateTimeRules::valid_time('1:30'));
    }

    public function testGetCreateRules()
    {
        $rules = Coaster::$createRules;

        $this->assertArrayHasKey(Coaster::KEY_STAFF, $rules);
        $this->assertArrayHasKey(Coaster::KEY_CLIENTS, $rules);
        $this->assertArrayHasKey(Coaster::KEY_LENGTH, $rules);
        $this->assertArrayHasKey(Coaster::KEY_HOUR_1, $rules);
        $this->assertArrayHasKey(Coaster::KEY_HOUR_2, $rules);

        $this->assertStringContainsString('required', $rules[Coaster::KEY_STAFF]);
        $this->assertStringContainsString('integer', $rules[Coaster::KEY_STAFF]);
        $this->assertStringContainsString('greater_than[0]', $rules[Coaster::KEY_STAFF]);

        $this->assertStringContainsString('required', $rules[Coaster::KEY_LENGTH]);
        $this->assertStringContainsString('integer', $rules[Coaster::KEY_LENGTH]);
        $this->assertStringContainsString('greater_than[0]', $rules[Coaster::KEY_LENGTH]);

        $this->assertStringContainsString('required', $rules[Coaster::KEY_HOUR_1]);
        $this->assertStringContainsString('valid_time', $rules[Coaster::KEY_HOUR_1]);

        $this->assertStringContainsString('required', $rules[Coaster::KEY_HOUR_2]);
        $this->assertStringContainsString('valid_time', $rules[Coaster::KEY_HOUR_2]);
    }

    public function testGetUpdateRules()
    {
        $rules = Coaster::$updateRules;

        $this->assertArrayHasKey(Coaster::KEY_STAFF, $rules);
        $this->assertArrayHasKey(Coaster::KEY_CLIENTS, $rules);
        $this->assertArrayHasKey(Coaster::KEY_HOUR_1, $rules);
        $this->assertArrayHasKey(Coaster::KEY_HOUR_2, $rules);

        $this->assertArrayNotHasKey(Coaster::KEY_LENGTH, $rules);
    }
}
