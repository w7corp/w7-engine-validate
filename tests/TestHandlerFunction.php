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

namespace W7\Tests;

use W7\Tests\Material\HandlerDataValidate;
use W7\Tests\Material\TestBaseValidate;

class TestHandlerFunction extends TestBaseValidate
{
    public function testAfterRule()
    {
        $v = new HandlerDataValidate();

        $this->expectExceptionMessage('用户信息重复');

        $v->scene('test')->check(['user' => [
            'a', 'a'
        ]]);
    }

    public function testAfterDataHandler()
    {
        $v = new HandlerDataValidate();

        $data = $v->scene('test')->check(['user' => [
            'a', 'b'
        ]]);

        $this->assertCount(3, $data['user']);
    }
}
