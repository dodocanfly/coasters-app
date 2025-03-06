<?php

namespace Tests\Unit\Entities;

use App\Entities\Wagon;
use CodeIgniter\Test\CIUnitTestCase;

class WagonEntityTest extends CIUnitTestCase
{
    public function testCreateWagon()
    {
        $data = [
            Wagon::KEY_ID => 'test_wagon_1',
            Wagon::KEY_COASTER_ID => 'test_coaster_1',
            Wagon::KEY_CAPACITY => 32,
            Wagon::KEY_SPEED => 1.2,
        ];

        $wagon = Wagon::fromArray($data);

        $this->assertEquals('test_wagon_1', $wagon->getId());
        $this->assertEquals('test_coaster_1', $wagon->getCoasterId());
        $this->assertEquals(32, $wagon->getCapacity());
        $this->assertEquals(1.2, $wagon->getSpeed());
    }

    public function testWagonToArray()
    {
        $wagon = new Wagon(
            'test_wagon_2',
            'test_coaster_2',
            40,
            1.5
        );

        $data = $wagon->toArray();

        $this->assertEquals([
            Wagon::KEY_ID => 'test_wagon_2',
            Wagon::KEY_COASTER_ID => 'test_coaster_2',
            Wagon::KEY_CAPACITY => 40,
            Wagon::KEY_SPEED => 1.5,
        ], $data);
    }

    public function testWagonTimePerRide()
    {
        $wagon = new Wagon(
            'test_wagon_3',
            'test_coaster_3',
            32,
            1.0
        );

        $this->assertEquals(30, $wagon->getTimePerRideInMinutes(1800));

        $wagon2 = new Wagon(
            'test_wagon_4',
            'test_coaster_4',
            32,
            2.0
        );

        $this->assertEquals(15, $wagon2->getTimePerRideInMinutes(1800));
    }
}
