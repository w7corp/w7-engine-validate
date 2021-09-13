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
use W7\Validate\Exception\ValidateRuntimeException;
use W7\Validate\Support\Concerns\MessageProviderInterface;
use W7\Validate\Support\MessageProvider;
use W7\Validate\Validate;

class TestMessageProvider extends MessageProvider implements MessageProviderInterface
{
}

class TestCustomMessageProvider extends BaseTestValidate
{
    /**
     * @test 测试多种方式设置消息处理器
     *
     * @throws \W7\Validate\Exception\ValidateException
     */
    public function testSetMessageProvider()
    {
        Validate::make()->setMessageProvider(TestMessageProvider::class);
        Validate::make()->setMessageProvider(new TestMessageProvider());
        Validate::make()->setMessageProvider(function () {
            return new TestMessageProvider();
        });

        $this->expectException(ValidateRuntimeException::class);
        Validate::make()->setMessageProvider('Test');
    }

    public function testGetMessage()
    {
        $messageProvider = new TestMessageProvider();
        $messageProvider->setData([
            'user' => 'admin',
            'pass' => '123456'
        ]);

        $messageProvider->setCustomAttributes([
            'user' => '账号',
            'pass' => '密码'
        ]);

        $message = $messageProvider->handleMessage('@{user}:{:user},@{pass}:{:pass}');
        $this->assertEquals('账号:admin,密码:123456', $message);

        $message = $messageProvider->handleMessage(['@{user}:{:user},@{pass}:{:pass}']);
        $this->assertEquals(['账号:admin,密码:123456'], $message);
    }
}
