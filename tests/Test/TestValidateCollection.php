<?php

/**
 * WeEngine System
 *
 * (c) We7Team 2021 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Tests\Test;

use W7\Tests\Material\BaseTestValidate;

class TestValidateCollection extends BaseTestValidate
{
    public function testGetMultiDimensionalArrays()
    {
        $data = [
            'a' => [
                'b' => [
                    'c' => 123
                ]
            ]
        ];

        $this->assertEquals(123, validate_collect($data)->get('a.b.c'));
    }

    public function testGetArrayPluck()
    {
        $data = [
            'I7 1700K' => [
                'vendor' => 'Inter'
            ],
            'R7 5800X' => [
                'vendor' => 'AMD'
            ],
            'I9 11900K' => [
                'vendor' => 'Inter'
            ],
            'A10-9700' => [
                'vendor' => 'AMD'
            ]
        ];

        $this->assertEquals([
            'Inter',
            'AMD',
            'Inter',
            'AMD'
        ], validate_collect($data)->get('*.vendor'));
    }

    public function testTypeConversion()
    {
        $data = [
            'age' => '20'
        ];

        $this->assertEquals(20, validate_collect($data)->int()->get('age'));
        $this->assertEquals([20], validate_collect($data)->array()->get('age'));
        $this->assertEquals(true, validate_collect($data)->bool()->get('age'));
        $this->assertEquals('20', validate_collect($data)->string()->get('age'));

        $data = [
            'age' => '20.05'
        ];

        $this->assertEquals(20, validate_collect($data)->int()->get('age'));
        $this->assertEquals(20.05, validate_collect($data)->float()->get('age'));
        $data = [
            'age' => 20
        ];

        $this->assertEquals('20', validate_collect($data)->string()->get('age'));
    }

    public function testShift()
    {
        $data = validate_collect([1, 2, 3, 4]);

        $this->assertEquals(1, $data->shift());
        $this->assertEquals(2, $data->shift());
        $this->assertEquals(3, $data->shift());
        $this->assertEquals(4, $data->shift());
        $this->assertEquals(null, $data->shift());
    }

    public function testPop()
    {
        $data = validate_collect([1, 2, 3, 4]);

        $this->assertEquals(4, $data->pop());
        $this->assertEquals(3, $data->pop());
        $this->assertEquals(2, $data->pop());
        $this->assertEquals(1, $data->pop());
        $this->assertEquals(null, $data->pop());
    }

    public function testPull()
    {
        $data = validate_collect([
            'name' => 'yuyu',
            'age'  => 2
        ]);

        $this->assertEquals('yuyu', $data->pull('name'));
        $this->assertFalse($data->has('name'));
        $this->assertEquals([
            'age' => 2
        ], $data->all());
    }

    public function testHas()
    {
        $data = validate_collect([
            'user' => [
                'name' => 'yuyu',
                'age'  => 1
            ]
        ]);

        $this->assertTrue($data->has('user'));
        $this->assertTrue($data->has('user.name'));
        $this->assertTrue($data->has('user.age'));
        $this->assertFalse($data->has('user.phone'));
    }

    public function testSet()
    {
        $data = validate_collect([
            'user' => [
                'name' => 'yuyu',
                'age'  => 1
            ]
        ]);

        $this->assertFalse($data->has('user.phone'));

        $data->set('user.phone', '13122223333');
        $this->assertTrue($data->has('user.phone'));
        $this->assertEquals('13122223333', $data->get('user.phone'));

        $data->set('count', 1);
        $this->assertTrue($data->has('count'));
        $this->assertEquals(1, $data->get('count'));
    }

    public function testWhenHas()
    {
        $data = validate_collect([
            'name' => 'yuyu'
        ]);
        $this->assertNull($data->get('have'));
        $data->whenHas('name', function ($data) {
            $data->set('have', true);
        });

        $this->assertTrue($data->get('have'));
    }

    public function testWhenNotHas()
    {
        $data = validate_collect();

        $this->assertNull($data->get('have'));

        $data->whenNotHas('name', function ($data) {
            $data->set('have', false);
        });

        $this->assertFalse($data->get('have'));
    }
}
