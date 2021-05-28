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
}
