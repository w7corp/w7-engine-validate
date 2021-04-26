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

use W7\Tests\Material\Event\Increasing;
use W7\Tests\Material\Event\SetDefault;
use W7\Validate\Validate;

class HandlerEventValidate extends Validate
{
    protected array $rule = [
        'a' => 'required',
        'b' => 'required',
        'i' => 'required|numeric'
    ];

    protected array $scene = [
        'setDefault' => ['a', 'b', 'handler' => [
            SetDefault::class => [[
                'a' => 1,
                'b' => 2
            ]]
        ]],

        'incr' => ['i', 'handler' => [
            Increasing::class => 'i'
        ]],

        'useIncr' => [
            'use' => 'incr'
        ]
    ];
}
