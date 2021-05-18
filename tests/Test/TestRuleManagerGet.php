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

        list($rule, $message, $customAttributes) = UserRulesManager::get();

        $this->assertEquals($_rule, $rule);
        $this->assertEquals($_message, $message);
        $this->assertEquals($_customAttributes, $customAttributes);
    }

    public function testGetStaticMethodForGetOnly()
    {
        $fields                                  = ['user', 'pass'];
        list($rule, $message, $customAttributes) = UserRulesManager::get($fields);
        $this->assertCount(2, $rule);
        $this->assertCount(2, $customAttributes);

        $this->assertEquals('用户名必须为邮箱', $message['user.email']);
        $this->assertEquals('密码长度错误', $message['pass.lengthBetween']);
    }

    public function testGetMethodForScene()
    {
        $userRules = new UserRulesManager();

        $this->assertCount(2, $userRules->scene('login')->getRules());
        $this->assertCount(2, $userRules->login()[0]);
        $this->assertCount(2, $userRules::login()[0]);
    }
}
