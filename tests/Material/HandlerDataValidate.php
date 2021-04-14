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
        'user.*' => 'chsAlphaNum',
        'name'   => 'required|Chs'
    ];

    protected array $scene = [
        'afterRule'                   => ['user', 'user.*', 'after' => 'checkUserNotRepeat'],
        'addData'                     => ['user', 'user.*', 'after' => 'addData'],
        'beforeHandlerData'           => ['name',  'before' => 'setDefaultName'],
        'beforeSetDefaultNameIsError' => ['name',  'before' => 'setDefaultNameIsError']
    ];

    public function afterAddData(array $data, $next)
    {
        $data['user'][] = 'c';
        return $next($data);
    }

    public function afterCheckUserNotRepeat(array $data, $next)
    {
        $uniqueData = array_unique($data['user']);

        if (count($data['user']) === count($uniqueData)) {
            return $next($data);
        }

        return '用户信息重复';
    }

    public function beforeSetDefaultName(array $data, $next)
    {
        if (isset($data['name']) || empty($data['name'])) {
            $data['name'] = '张三';
        }

        return $next($data);
    }

    public function beforeSetDefaultNameIsError(array $data, $next)
    {
        if (isset($data['name']) || empty($data['name'])) {
            $data['name'] = 'test';
        }

        return $next($data);
    }
}
