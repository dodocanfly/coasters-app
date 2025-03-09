<?php

namespace Tests\Unit\Entities;

use App\Entities\Coaster;
use CodeIgniter\Test\CIUnitTestCase;

class CoasterEntityTest extends CIUnitTestCase
{
    public function testCreateCoaster()
    {
        $data = [
            Coaster::KEY_ID => 'test_coaster_1',
            Coaster::KEY_STAFF => 10,
            Coaster::KEY_CLIENTS => 5000,
            Coaster::KEY_CAPACITY => 32,
            Coaster::KEY_SPEED => 1.2,
            Coaster::KEY_LENGTH => 1500,
            Coaster::KEY_HOUR_1 => '8:00',
            Coaster::KEY_HOUR_2 => '16:00',
        ];

        $coaster = Coaster::fromArray($data);

        $this->assertEquals('test_coaster_1', $coaster->getId());
        $this->assertEquals(10, $coaster->getNumberOfStaff());
        $this->assertEquals(5000, $coaster->getNumberOfClients());
        $this->assertEquals(1500, $coaster->getRouteLength());
        $this->assertEquals('8:00', $coaster->getOpeningTime());
        $this->assertEquals('16:00', $coaster->getClosingTime());
    }

    public function testCoasterToArray()
    {
        $coaster = new Coaster(
            'test_coaster_2',
            15,
            6000,
            32,
            1.2,
            1800,
            '9:00',
            '17:00'
        );

        $data = $coaster->toArray();

        $this->assertEquals([
            Coaster::KEY_ID => 'test_coaster_2',
            Coaster::KEY_STAFF => 15,
            Coaster::KEY_CLIENTS => 6000,
            Coaster::KEY_CAPACITY => 32,
            Coaster::KEY_SPEED => 1.2,
            Coaster::KEY_LENGTH => 1800,
            Coaster::KEY_HOUR_1 => '9:00',
            Coaster::KEY_HOUR_2 => '17:00',
        ], $data);
    }

    public function testCoasterWorkingTimeInMinutes()
    {
        $coaster = new Coaster(
            'test_coaster_3',
            10,
            5000,
            32,
            1.2,
            1500,
            '8:00',
            '16:00'
        );

        $this->assertEquals(480, $coaster->getWorkingTimeInMinutes());

        $coaster2 = new Coaster(
            'test_coaster_4',
            10,
            5000,
            32,
            1.2,
            1500,
            '9:30',
            '14:45'
        );

        $this->assertEquals(315, $coaster2->getWorkingTimeInMinutes());
    }
}
