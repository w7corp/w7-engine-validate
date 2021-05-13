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

use W7\Tests\Material\ArticleValidate;
use W7\Tests\Material\BaseTestValidate;
use W7\Tests\Material\Rules\Length;
use W7\Validate\Exception\ValidateException;
use W7\Validate\RuleManager;
use W7\Validate\Support\Concerns\SceneInterface;

class UserRules extends RuleManager
{
    protected $rule = [
        'user'    => 'required|email',
        'pass'    => 'required|lengthBetween:6,16',
        'name'    => 'required|chs|lengthBetween:2,4',
        'remark'  => 'required|alpha_dash',
        'captcha' => 'required|length:4|checkCaptcha',
    ];

    protected $scene = [
        'login'   => ['user', 'pass'],
        'captcha' => ['captcha']
    ];

    protected $message = [
        'captcha.checkCaptcha' => '验证码错误'
    ];

    protected function sceneRegister(SceneInterface $scene)
    {
        return $scene->only(['user', 'pass', 'name', 'remark'])
            ->remove('remark', 'required|alpha_dash')
            ->append('remark', 'chs');
    }

    protected function sceneRegisterNeedCaptcha(SceneInterface $scene)
    {
        return $this->sceneRegister($scene)->appendCheckField('captcha');
    }

    public function ruleCheckCaptcha($att, $value): bool
    {
        return true;
    }
}

class TestValidateScene extends BaseTestValidate
{
    protected $userInput;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->userInput = [
            'content' => '内容',
            'title'   => '这是一个标题'
        ];
    }

    public function testGetAllRule()
    {
        $this->assertCount(5, (new UserRules())->getRules());
    }

    public function testSceneIsLogin()
    {
        $userRule = new UserRules();

        $needRules = [
            'user' => 'required|email',
            'pass' => 'required|lengthBetween:6,16'
        ];

        $this->assertEquals($needRules, $userRule->scene('login')->getRules());
        $this->assertEquals($needRules, $userRule->getRules('login'));
    }

    public function testCustomValidateScene()
    {
        $userRule = new UserRules();
        $rules    = $userRule->scene('register')->getRules();

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
        $userRule = new UserRules();

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

    /**
     * @test 测试没有指定验证场景的情况
     * @throws ValidateException
     */
    public function testNotScene()
    {
        $v = new ArticleValidate();
        $this->expectException(ValidateException::class);
        $v->check($this->userInput);
    }

    /**
     * @test 测试制定验证场景
     * @throws ValidateException
     */
    public function testScene()
    {
        $v    = new ArticleValidate();
        $data = $v->scene('add')->check($this->userInput);
        $this->assertEquals('内容', $data['content']);
    }

    /**
     * @test 测试自定义验证场景 edit
     * @throws ValidateException
     */
    public function testCustomScene()
    {
        $validate = new ArticleValidate();
        $this->expectException(ValidateException::class);
        $validate->scene('edit')->check($this->userInput);
    }

    /**
     * @test 测试Use自定义验证场景 save
     * @throws ValidateException
     */
    public function testUseScene()
    {
        $validate = new ArticleValidate();
        $this->expectExceptionMessage('缺少参数：文章Id');
        $validate->scene('save')->check($this->userInput);
    }

    /**
     * @test 测试移除规则
     * @throws ValidateException
     */
    public function testDynamicScene()
    {
        $validate = new ArticleValidate();
        $data     = $validate->scene('dynamic')->check([
            'title'   => '标题标题标题标题',
            'content' => '1'
        ]);
        $this->assertEquals('1', $data['content']);
    }
}
