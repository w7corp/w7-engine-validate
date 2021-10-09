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

class TestRegexRule extends BaseTestValidate
{
    /**
     * @test 测试正则表达式`regex`规则是否可以正常使用
     * @throws ValidateException
     */
    public function testRegexRule()
    {
        $v                   = new class extends Validate {
            protected $regex = [
                'number' => '/^\d+$/'
            ];

            protected $rule = [
                'num' => 'required|regex:number'
            ];

            protected $message = [
                'num.regex' => '给定的值必须是数字'
            ];
        };

        $data = $v->check([
            'num' => '123'
        ]);
        $this->assertEquals('123', $data['num']);
        
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^给定的值必须是数字$/');
        $v->check([
            'num' => 'sss'
        ]);
    }

    /**
     * @test 测试正则表达式`not_regex`规则是否可以正常使用
     * @throws ValidateException
     */
    public function testNotRegexRule()
    {
        $v                   = new class extends Validate {
            protected $regex = [
                'number' => '/^\d+$/'
            ];

            protected $rule = [
                'user' => 'required|not_regex:number'
            ];

            protected $message = [
                'user.not_regex' => '给定的值不可以为纯数字'
            ];
        };

        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^给定的值不可以为纯数字$/');
        $v->check([
            'user' => '123'
        ]);

        $data = $v->check([
            'user' => 'a1b2'
        ]);
        $this->assertEquals('a1b2', $data['user']);
    }

    /**
     * @test 测试正则表示式规则在验证场景中的使用
     * @throws ValidateException
     */
    public function testRegexRuleInScene()
    {
        $v                   = new class extends Validate {
            protected $regex = [
                'status' => '/^0|1|on|off|true|false$/'
            ];

            protected $rule = [
                'status' => 'required'
            ];

            protected function sceneTest(ValidateScene $scene)
            {
                $scene->only(['status'])
                    ->append('status', 'regex:status');

                $this->setMessages([
                    'status.regex' => '状态不符合要求'
                ]);
            }
        };

        $data = $v->scene('test')->check([
            'status' => 1
        ]);
        $this->assertSame(1, $data['status']);

        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^状态不符合要求$/');
        $v->scene('test')->check([
            'status' => 'close'
        ]);
    }
}
