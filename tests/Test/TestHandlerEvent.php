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
use W7\Tests\Material\HandlerEventValidate;
use W7\Tests\Material\TestValidate;
use W7\Validate\Exception\ValidateException;

class TestHandlerEvent extends BaseTestValidate
{
    public function testErrorEvent()
    {
        $v = new TestValidate();
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('不是中文');
        $v->scene('errorEvent')->check([
            'name' => 123
        ]);
    }

    public function testEventIsCheckName()
    {
        $v = new TestValidate();
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('用户名不是admin');
        $v->scene('checkName')->check([
            'name' => 123
        ]);
    }

    public function testBeforeThrowError()
    {
        $v = new TestValidate();
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('error');
        $v->scene('beforeThrowError')->check([]);
    }

    /**
     * @test 测试使用事件设置默认值
     * @throws ValidateException
     */
    public function testEventSetDefault()
    {
        $v = new HandlerEventValidate();

        $data = $v->scene('setDefault')->check([
            'a' => 567
        ]);

        $this->assertEquals(567, $data['a']);
        $this->assertEquals(2, $data['b']);
    }

    /**
     * @test 测试使用事件来使指定的字段递增
     * @throws ValidateException
     */
    public function testIncreasing()
    {
        $v = new HandlerEventValidate();

        $data = $v->scene('incr')->check([
            'i' => 1
        ]);

        $this->assertEquals(2, $data['i']);
    }

    /**
     * @test 测试当验证场景中使用了use，被use的场景中的事件是否生效
     * @throws ValidateException
     */
    public function testUseSceneForIncreasing()
    {
        $v = new HandlerEventValidate();

        $data = $v->scene('useIncr')->check([
            'i' => 1
        ]);

        $this->assertEquals(2, $data['i']);

        $data = $v->scene('useIncr')->check($data);

        $this->assertEquals(3, $data['i']);
    }
}
