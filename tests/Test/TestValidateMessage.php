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
use W7\Validate\Support\Event\ValidateEventAbstract;
use W7\Validate\Support\MessageProvider;
use W7\Validate\Support\ValidateScene;
use W7\Validate\Validate;

class TestErrorMessageEvent extends ValidateEventAbstract
{
    public function afterValidate(): bool
    {
        $this->message = 'user.required';
        return false;
    }
}
class TestMessage extends Validate
{
    protected $rule = [
        'user'    => 'required|email',
        'pass'    => 'required|lengthBetween:6,16',
        're_pass' => 'required|eq:pass',
        'name'    => 'required|chs|lengthBetween:2,4',
        'remark'  => 'required|alpha_dash',
        'captcha' => 'required|length:4|checkCaptcha',
    ];

    protected $message = [
        'user.required' => '用户名必须填写',
        'user.email'    => '你输入的:{:user}，不是有效的:attribute',
        'pass.required' => '密码必须填写',
        're_pass.eq'    => '你输入的{@pass}与:attribute不一致'
    ];

    protected $customAttributes = [
        'user'    => '用户名',
        'pass'    => '密码',
        're_pass' => '确认密码',
        'name'    => '昵称',
        'remark'  => '备注',
        'captcha' => '验证码',
    ];

    protected function sceneTestSceneEventClosure(ValidateScene $scene)
    {
        $scene->after(function () {
            return 'user.required';
        });
    }

    protected function sceneTestSceneEventCallable(ValidateScene $scene)
    {
        $scene->before('testMessage');
    }

    protected function sceneTestSceneEvent(ValidateScene $scene)
    {
        $scene->event(TestErrorMessageEvent::class);
    }

    protected function beforeTestMessage(): string
    {
        return 'pass.required';
    }
}

class TestValidateMessage extends BaseTestValidate
{
    /** @var TestMessage */
    protected $testMessage;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->testMessage = new TestMessage();
    }

    /**
     * @test 测试消息处理器对错误消息的处理是否符合预期
     */
    public function testGetCustomMessage()
    {
        $message = (new MessageProvider())->setRuleManager($this->testMessage);
        $this->assertEquals('用户名必须填写', $message->getInitialMessage('user', 'required'));
        $this->assertEquals('你输入的:{:user}，不是有效的:attribute', $message->getInitialMessage('user', 'email'));

        $this->assertEquals('你输入的:123456，不是有效的:attribute', $message->setData([
            'user' => '123456'
        ])->getMessage('user', 'email'));

        $this->assertEquals('你输入的密码与:attribute不一致', $message->getMessage('re_pass', 'eq'));
    }

    /**
     * @test 测试在验证场景中的事件方法中(闭包方式)返回错误
     *
     * @throws ValidateException
     */
    public function testMessageInSceneEventClosure()
    {
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^用户名必须填写$/');
        $this->testMessage->scene('testSceneEventClosure')->check([]);
    }

    /**
     * @test 测试在验证场景中的事件方法中(Callable方式)返回错误
     *
     * @throws ValidateException
     */
    public function testMessageInSceneEventCallable()
    {
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^密码必须填写$/');
        $this->testMessage->scene('testSceneEventCallable')->check([]);
    }

    /**
     * @test 测试在验证场景中的事件返回错误
     *
     * @throws ValidateException
     */
    public function testMessageInSceneEvent()
    {
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^用户名必须填写$/');
        $this->testMessage->scene('testSceneEvent')->check([]);
    }
}
