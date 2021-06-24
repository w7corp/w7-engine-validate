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
use W7\Validate\Validate;

class TestValidateSceneUse extends BaseTestValidate
{
    /**
     * @test 测试use以及场景选择器的连续使用
     * @throws ValidateException
     */
    public function testUse()
    {
        $testValidate       = new class extends Validate {
            protected $rule = [
                'a' => 'required',
                'b' => 'required',
                'c' => 'required',
                'd' => 'required',
                'e' => 'required',

                'f' => 'required',
                'g' => 'required',

                'h' => 'required',
                'i' => 'required',

                'not' => ' required'
            ];

            protected $scene = [
                'testA' => ['a', 'b', 'c', 'next' => 'testB'],
                'testB' => ['c', 'd', 'next' => 'testC'],
                'testC' => ['e', 'next' => 'checkCode'],
                'testD' => ['f', 'g', 'next' => 'testE'],
                'testE' => ['h', 'i'],
            ];

            protected function checkCodeSelector(array $data)
            {
                return 'testD';
            }
        };

        $data = $testValidate::make()->scene('testA')->check([
            'a' => 1,
            'b' => '666',
            'c' => '456',
            'd' => '585',
            'e' => 1,

            'f' => 2,
            'g' => 2,

            'h' => 12,
            'i' => 23
        ]);
        
        $this->assertCount(9, $data);
    }
}
