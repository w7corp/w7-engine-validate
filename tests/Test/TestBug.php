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
}
