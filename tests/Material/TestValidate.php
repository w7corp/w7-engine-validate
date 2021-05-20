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

namespace W7\Tests\Material;

use W7\Tests\Material\Event\CheckIsChs;
use W7\Validate\Validate;

class TestValidate extends Validate
{
    protected $rule = [
        'name' => 'required'
    ];

    protected $scene = [
        'errorEvent'       => ['name', 'event' => [CheckIsChs::class => ['name']]],
        'checkName'        => ['name', 'after' => ['checkNameIsAdmin' => 'name']],
        'beforeThrowError' => ['before' => 'throwError']
    ];

    protected function afterCheckNameIsAdmin($data, $field)
    {
        if (($data[$field] ?? '') === 'admin') {
            return true;
        }

        return '用户名不是admin';
    }

    protected function beforeThrowError($data)
    {
        return 'error';
    }
}
