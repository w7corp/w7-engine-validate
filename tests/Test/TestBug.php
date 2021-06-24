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
use W7\Validate\Support\ValidateScene;
use W7\Validate\Validate;

class TestBug extends BaseTestValidate
{
    public function testBug5()
    {
        $v                    = new class extends Validate {
            public $checkData = [];

            protected $rule = [
                'name' => 'required'
            ];

            protected function sceneTest(ValidateScene $scene)
            {
                $this->checkData = $scene->getData();
                $scene->only(['name']);
            }
        };

        $v->scene('test')->check([
            'name' => 123
        ]);

        $this->assertArrayHasKey('name', $v->checkData);
        $this->assertEquals(123, $v->checkData['name']);
    }

    public function testBug6()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'a' => 'required',
                'b' => 'required',
                'c' => 'required',
            ];

            protected $message = [
                'a.required' => 'a不能为空',
                'b.required' => 'b不能为空',
                'c.required' => 'c不能为空',
            ];

            protected function sceneTest(ValidateScene $scene)
            {
                $scene->only(['a'])
                    ->appendCheckField('b')
                    ->appendCheckField('c');
            }
        };

        try {
            $v->scene('test')->check([
                'a' => 1
            ]);
        } catch (ValidateException $e) {
            $this->assertEquals('b不能为空', $e->getMessage());
        }

        try {
            $v->scene('test')->check([
                'a' => 1,
                'b' => 1
            ]);
        } catch (ValidateException $e) {
            $this->assertEquals('c不能为空', $e->getMessage());
        }

        $data = $v->scene('test')->check([
            'a' => 1,
            'b' => 1,
            'c' => 1,
        ]);

        $this->assertEmpty(array_diff_key($data, array_flip(['a', 'b', 'c'])));
    }
}
