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
use W7\Validate\Support\Concerns\FilterInterface;
use W7\Validate\Support\ValidateScene;
use W7\Validate\Validate;

class UniqueFilter implements FilterInterface
{
    public function handle($value)
    {
        return array_unique($value);
    }
}
class TestDataFilter extends BaseTestValidate
{
    public function testSetFilterIsSystemMethod()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'id' => 'required|numeric'
            ];

            protected $filter = [
                'id' => 'intval'
            ];
        };

        $data = $v->check(['id' => '1']);

        $this->assertTrue(1 === $data['id']);
    }

    public function testSetFilterIsClassMethod()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'id' => 'required'
            ];

            protected $filter = [
                'id' => 'toArray'
            ];

            public function filterToArray($value)
            {
                return explode(',', $value);
            }
        };

        $data = $v->check(['id' => '1,2,3,4,5']);

        $this->assertEquals([1, 2, 3, 4, 5], $data['id']);
    }

    public function testSetFilterIsFilterClass()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'id'   => 'required|array',
                'id.*' => 'numeric'
            ];

            protected $filter = [
                'id' => UniqueFilter::class
            ];
        };

        $data = $v->check(['id' => [1, 1, 2, 3, 4, 4, 5, 6, 7]]);

        $this->assertEquals([1, 2, 3, 4, 5, 6, 7], array_values($data['id']));
    }

    public function testSetFilterForArrayField()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'id'   => 'required|array',
                'id.*' => 'numeric'
            ];

            protected $filter = [
                'id.*' => 'intval'
            ];
        };

        $data = $v->check(['id' => ['1', '2', 3, '4']]);

        foreach ($data['id'] as $id) {
            $this->assertEquals('integer', gettype($id));
        }
    }

    public function testCancelFilter()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'id' => 'required'
            ];

            protected $filter = [
                'id' => 'intval'
            ];

            protected function sceneTest(ValidateScene $scene)
            {
                $scene->only(['id'])
                    ->filter('id', null);
            }
        };

        $data = $v->check(['id' => '你好']);
        $this->assertEquals(0, $data['id']);
        $data = $v->scene('test')->check(['id' => '你好']);
        $this->assertEquals('你好', $data['id']);
    }
}
