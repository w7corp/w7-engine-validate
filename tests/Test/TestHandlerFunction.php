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

use W7\Tests\Material\HandlerDataValidate;
use W7\Tests\Material\TestBaseValidate;

class TestHandlerFunction extends TestBaseValidate
{
    /**
     * @test 测试在after中进行最后的验证
     * @throws \W7\Validate\Exception\ValidateException
     */
    public function testAfterRule()
    {
        $v = new HandlerDataValidate();

        $this->expectExceptionMessage('用户信息重复');

        $v->scene('afterRule')->check(['user' => [
            'a', 'a'
        ]]);
    }

    /**
     * @test 测试在after方法中对数据进行处理
     * @throws \W7\Validate\Exception\ValidateException
     */
    public function testAfterAddData()
    {
        $v = new HandlerDataValidate();

        $data = $v->scene('addData')->check(['user' => [
            'a', 'b'
        ]]);

        $this->assertCount(3, $data['user']);
    }

    /**
     * @test 测试在before方法中对值设定一个默认值
     * @throws \W7\Validate\Exception\ValidateException
     */
    public function testBeforeHandlerData()
    {
        $v = new HandlerDataValidate();

        $data = $v->scene('beforeHandlerData')->check([]);

        $this->assertEquals('张三', $data['name']);

        $data = $v->scene('beforeHandlerData')->check(['name' => '李四']);

        $this->assertEquals('李四', $data['name']);
    }

    /**
     * @test 测试在before方法中对值设定一个不符合规则的默认值
     * @throws \W7\Validate\Exception\ValidateException
     */
    public function testBeforeHandlerToVerifySetDefaultValues()
    {
        $v = new HandlerDataValidate();

        $this->expectExceptionMessage('名称的值只能具有中文');

        $v->scene('beforeSetDefaultNameIsError')->check([]);
    }
}
