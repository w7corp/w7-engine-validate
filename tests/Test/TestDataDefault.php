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
use W7\Validate\Exception\ValidateException;
use W7\Validate\Support\Concerns\DefaultInterface;
use W7\Validate\Support\DataAttribute;
use W7\Validate\Support\ValidateScene;
use W7\Validate\Validate;

class SetDefaultIsHello implements DefaultInterface
{
    public function handle($value, string $attribute, array $originalData)
    {
        return 'Hello';
    }
}

class TestDataDefault extends BaseTestValidate
{
    public function testDefaultIsScalar()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'name' => 'required'
            ];

            protected $default = [
                'name' => '123'
            ];
        };

        $data = $v->check([]);

        $this->assertSame('123', $data['name']);
    }

    public function testDefaultIsArray()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'name' => 'required'
            ];

            protected $default = [
                'name' => ['a', 'b', 'any' => 123]
            ];
        };

        $data = $v->check([]);

        $this->assertEquals(['a', 'b', 'any' => 123], $data['name']);

        $v                  = new class extends Validate {
            protected $rule = [
                'name' => 'required'
            ];

            protected $default = [
                'name' => ['value' => ['a', 'b'], 'any' => true]
            ];
        };

        $data = $v->check([]);

        $this->assertEquals(['a', 'b'], $data['name']);
    }

    public function testDefaultIsCallback()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'name' => 'required',
                'age'  => 'required|numeric',
                'sex'  => 'required'
            ];

            public function __construct()
            {
                $this->default = [
                    'name' => function ($value) {
                        return '小张';
                    },
                    'age' => [$this, 'setAge'],
                    'sex' => 'setSex'
                ];
            }

            public function setAge($value)
            {
                return 100;
            }
            
            public function defaultSetSex($value)
            {
                return '男';
            }
        };

        $data = $v->check([]);

        $this->assertEquals(['name' => '小张', 'age' => 100, 'sex' => '男'], $data);
    }

    public function testHandlerData()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'id' => 'required'
            ];

            public function __construct()
            {
                $this->default = [
                    'id' => ['value' => function ($value) {
                        if (is_string($value)) {
                            return explode(',', $value);
                        }
                        return $value;
                    }, 'any' => true]
                ];
            }
        };

        $data = $v->check([
            'id' => '1,2,3,4'
        ]);

        $this->assertEquals([1, 2, 3, 4], $data['id']);
    }

    public function testDefaultForScene()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'name' => 'required'
            ];

            protected function sceneTest(ValidateScene $scene)
            {
                $scene->only(['name'])
                    ->default('name', '小张');
            }
        };
        $this->expectException(ValidateException::class);
        $v->check([]);

        $data = $v->scene('test')->check([]);
        $this->assertSame('小张', $data['name']);
    }

    public function testCancelDefaultValue()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'name' => ''
            ];

            protected $default = [
                'name' => 1
            ];

            protected function sceneTest(ValidateScene $scene)
            {
                $scene->only(['name'])
                    ->default('name', null);
            }
        };

        $data = $v->check([]);
        $this->assertEquals(1, $data['name']);

        $data = $v->scene('test')->check([]);
        $this->assertArrayNotHasKey('name', $data);
    }

    public function testDefaultUseDefaultClass()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'name' => ''
            ];

            protected $default = [
                'name' => SetDefaultIsHello::class
            ];
        };

        $data = $v->check([]);
        $this->assertSame('Hello', $data['name']);
    }

    public function testDefaultDeleteField()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'a' => 'numeric'
            ];

            protected $default = [
                'a' => ['value' => 'deleteField', 'any' => true]
            ];

            public function defaultDeleteField($value, $field, $data, DataAttribute $dataAttribute)
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
}
