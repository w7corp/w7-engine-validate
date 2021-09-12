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
use W7\Tests\Material\UserRulesManager;
use W7\Validate\RuleManager;

class TestRuleManagerGet extends BaseTestValidate
{
    public function testGetStaticMethodForGetAll()
    {
        $userRules = new UserRulesManager();
        $ref       = new \ReflectionClass($userRules);
        $_rule     = $ref->getProperty('rule');
        $_rule->setAccessible(true);
        $_rule = $_rule->getValue($userRules);

        $_message = $ref->getProperty('message');
        $_message->setAccessible(true);
        $_message = $_message->getValue($userRules);

        $_customAttributes = $ref->getProperty('customAttributes');
        $_customAttributes->setAccessible(true);
        $_customAttributes = $_customAttributes->getValue($userRules);

        list($rule, $message, $customAttributes) = UserRulesManager::get(null, true);

        $this->assertEquals($_rule, $rule);
        $this->assertEquals($_message, $message);
        $this->assertEquals($_customAttributes, $customAttributes);
    }

    public function testGetStaticMethodForGetOnly()
    {
        $fields                                  = ['user', 'pass'];
        list($rule, $message, $customAttributes) = UserRulesManager::get($fields, true);
        $this->assertCount(2, $rule);
        $this->assertCount(2, $customAttributes);

        $this->assertEquals('用户名必须为邮箱', $message['user.email']);
        $this->assertEquals('密码长度错误', $message['pass.lengthBetween']);
    }

    public function testGetMethodForScene()
    {
        $userRules = new UserRulesManager();

        $this->assertCount(2, $userRules->scene('login')->getRules());
        $this->assertCount(2, $userRules::login()[0]);
    }

    /**
     * @test 测试清空规则管理器的场景
     */
    public function testClearScene()
    {
        $v                  = new class extends RuleManager {
            protected $rule = [
                'user' => 'required',
                'pass' => 'required',
                'name' => 'required'
            ];

            protected $scene = [
                'login' => ['user', 'pass']
            ];
        };

        $rules = $v->setScene()->scene('login')->getRules();
        $this->assertSame(3, \count(array_intersect(array_keys($rules), ['user', 'pass', 'name'])));
    }

    /**
     * @test 测试清空规则管理器的规则
     */
    public function testClearRules()
    {
        $v                  = new class extends RuleManager {
            protected $rule = [
                'user' => 'required',
                'pass' => 'required',
                'name' => 'required'
            ];
        };

        $rules = $v->setRules()->getRules();
        $this->assertEmpty($rules);
    }

    /**
     * @test 测试Message方法
     */
    public function testMessageMethod()
    {
        $v                  = new class extends RuleManager {
            protected $rule = [
                'user' => 'required|number',
                'pass' => 'required',
            ];

            protected $message = [
                'code.required' => 'code不可为空',
            ];
        };

        $message = $v->setMessages()
            ->setMessages([
                'pass.required' => 'pass不可为空',
                'user.number'   => '账号必须为纯数字'
            ])
            ->setMessages([
                'user.required' => '账号不可为空',
                'pass.required' => '密码不可为空'
            ])->getMessages();

        $this->assertEquals([
            'user.required' => '账号不可为空',
            'pass.required' => '密码不可为空',
            'user.number'   => '账号必须为纯数字'
        ], $message);

        $this->assertEquals('账号不可为空', $v->getMessages('user.required'));
        $this->assertEquals('账号不可为空', $v->getMessages('user', 'required'));
        $this->assertEquals([
            'user.required' => '账号不可为空',
            'pass.required' => '密码不可为空',
        ], $v->getMessages(['user.required', 'pass.required']));

        $this->assertEquals([
            'user.required' => '账号不可为空',
            'user.number'   => '账号必须为纯数字',
        ], $v->getMessages('user', null, true));
    }

    /**
     * @test 测试CustomAttributes方法
     */
    public function testCustomAttributesMethod()
    {
        $v                              = new class extends RuleManager {
            protected $customAttributes = [
                'user' => '账号'
            ];
        };

        $v->setCustomAttributes();

        $this->assertEmpty($v->getCurrentSceneName());

        $v->setCustomAttributes([
            'user' => '账号'
        ])->setCustomAttributes([
            'user' => '用户名',
            'pass' => '密码'
        ]);

        $this->assertEquals([
            'user' => '用户名',
            'pass' => '密码'
        ], $v->getCustomAttributes());

        $this->assertEquals([
            'user' => '用户名',
            'pass' => '密码'
        ], $v->getCustomAttributes(['user', 'pass']));
    }
}
