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
use W7\Validate\Validate;

class TestHandlerFunction extends BaseTestValidate
{
    public function testAfterFunction()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'id' => 'required'
            ];

            protected $scene = [
                'testAfter' => ['id', 'after' => 'checkId']
            ];

            protected function afterCheckId($data)
            {
                if ($data['id'] < 0) {
                    return 'ID错误';
                }
                return true;
            }
        };

        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('ID错误');

        $v->scene('testAfter')->check(['id' => -1]);
    }

    public function testBeforeFunction()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'id' => 'required'
            ];

            protected $scene = [
                'testBefore' => ['id', 'before' => 'checkSiteStatus']
            ];

            protected function beforeCheckSiteStatus(array $data)
            {
                return '站点未开启';
            }
        };

        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('站点未开启');

        $v->scene('testBefore')->check([]);
    }
}
