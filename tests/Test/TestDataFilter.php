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
use W7\Validate\Exception\ValidateRuntimeException;
use W7\Validate\Support\Concerns\FilterInterface;
use W7\Validate\Support\DataAttribute;
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
            $this->assertSame('integer', gettype($id));
        }
    }

    /**
     * @test 测试当数据不存在时，过滤器的处理
     *
     * @throws \W7\Validate\Exception\ValidateException
     */
    public function testNotHasDataFilter()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'id' => 'numeric'
            ];

            protected $filter = [
                'id' => 'intval'
            ];
        };

        $data = $v->check([]);
        $this->assertArrayNotHasKey('id', $data);
    }

    /**
     * @test 测试场景中取消设置过滤器
     *
     * @throws \W7\Validate\Exception\ValidateException
     */
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
        $this->assertSame(0, $data['id']);
        $data = $v->scene('test')->check(['id' => '你好']);
        $this->assertSame('你好', $data['id']);
    }

    /**
     * @test 测试过滤器中删除字段
     *
     * @throws \W7\Validate\Exception\ValidateException
     */
    public function testFilterDeleteField()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'a' => ''
            ];

            protected $filter = [
                'a' => 'deleteField'
            ];

            public function filterDeleteField($value, DataAttribute $dataAttribute)
            {
                $dataAttribute->deleteField = true;
                return '';
            }
        };

        $data = $v->check([
            'a' => 123
        ]);

        $this->assertTrue(empty($data));
    }

    /**
     * @test 测试不存在的过滤器
     *
     * @throws \W7\Validate\Exception\ValidateException
     */
    public function testNonexistentFilter()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'a' => 'required'
            ];

            protected function sceneTest(ValidateScene $scene)
            {
                $scene->only(['a'])->filter('a', 'test');
            }
        };

        $this->expectException(ValidateRuntimeException::class);

        $v->scene('test')->check([
            'a' => 123
        ]);
    }
}
