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

use W7\Validate\Validate;

class HandlerDataValidate extends Validate
{
    protected array $rule = [
        'user'   => 'required|array',
        'user.*' => 'chsAlphaNum'
    ];

    protected array $scene = [
        'test' => ['user', 'user.*', 'after' => 'checkUserNotRepeat']
    ];

    public function afterCheckUserNotRepeat(array $data, $next)
    {
        $uniqueData = array_unique($data['user']);

        if (count($data['user']) === count($uniqueData)) {
            $data['user'][] = 'c';
            return $next($data);
        }

        return '用户信息重复';
    }
}
