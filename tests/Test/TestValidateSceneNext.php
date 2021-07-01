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
use W7\Tests\Material\Count;
use W7\Validate\Exception\ValidateException;
use W7\Validate\Validate;

class TestValidateSceneNext extends BaseTestValidate
{
    /**
     * @test 测试Next以及场景选择器的连续使用
     * @throws ValidateException
     */
    public function testNext()
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

            protected function checkCodeSelector(): string
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

    /**
     * @test 测试多个场景指定了同一个字段，是否在一个验证链中，只验证一次
     * @throws ValidateException
     */
    public function testNextValidationCountIsOnce()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'a' => 'required|tests'
            ];

            protected $scene = [
                'testA' => ['a', 'next' => 'testB'],
                'testB' => ['a', 'next' => 'testC'],
                'testC' => ['a']
            ];

            protected function ruleTests()
            {
                Count::incremental('ruleTest');
                return true;
            }
        };

        $data = $v->scene('testA')->check(['a' => 1]);
        $this->assertEquals(1, $data['a']);
        Count::assertEquals(1, 'ruleTest');

        Count::reset('ruleTest');
        $data = $v->scene('testB')->check(['a' => 1]);
        $this->assertEquals(1, $data['a']);
        Count::assertEquals(1, 'ruleTest');

        Count::reset('ruleTest');
        $data = $v->scene('testC')->check(['a' => 1]);
        $this->assertEquals(1, $data['a']);
        Count::assertEquals(1, 'ruleTest');
    }
}
