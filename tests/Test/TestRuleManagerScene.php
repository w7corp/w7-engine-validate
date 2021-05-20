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
use W7\Tests\Material\Rules\Length;
use W7\Tests\Material\UserRulesManager;

class TestRuleManagerScene extends BaseTestValidate
{
    public function testGetAllRule()
    {
        $this->assertCount(5, (new UserRulesManager())->getRules());
    }

    public function testSceneIsLogin()
    {
        $userRule = new UserRulesManager();

        $needRules = [
            'user' => 'required|email',
            'pass' => 'required|lengthBetween:6,16'
        ];

        $this->assertEquals($needRules, $userRule->scene('login')->getRules(null,true));
        $this->assertEquals($needRules, $userRule->getRules(null, 'login'));
        $this->assertEquals($needRules, $userRule->getInitialRules('login'));
    }

    public function testCustomValidateScene()
    {
        $userRule = new UserRulesManager();
        $rules    = $userRule->scene('register')->getRules(null,true);

        $this->assertCount(4, $rules);
        $this->assertArrayHasKey('remark', $rules);
        $this->assertFalse(in_array('alpha_dash', $rules['remark']));
        $this->assertTrue(in_array('chs', $rules['remark']));

        $rules = $userRule->scene('registerNeedCaptcha')->getRules();
        $this->assertCount(5, $rules);
        $this->assertArrayHasKey('captcha', $rules);
    }

    public function testExtendsRule()
    {
        $userRule = new UserRulesManager();

        $rules = $userRule->scene('captcha')->getCheckRules();

        $this->assertArrayHasKey('captcha', $rules);
        $haveRequiredRule = $haveCustomRule = $haveExtendRule = $extendRuleName = false;
        foreach ($rules['captcha'] as $rule) {
            switch ($rule) {
                case 'required':
                    $haveRequiredRule = true;
                    break;
                case $rule instanceof Length:
                    $haveCustomRule = true;
                    break;
                case 32 === strlen($rule):
                    $haveExtendRule = true;
                    $extendRuleName = $rule;
                    break;
            }
        }

        $this->assertTrue($haveRequiredRule);
        $this->assertTrue($haveCustomRule);
        $this->assertTrue($haveExtendRule);

        $messages = $userRule->getMessages();

        $this->assertEquals('验证码错误', $messages['captcha.' . $extendRuleName]);
    }
}
