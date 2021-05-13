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

namespace W7\Tests\Material\Rules;

use W7\Validate\Support\Rule\BaseRule;

/**
 * 通过字节的方式来限定长度
 * @package W7\App\Model\Validate\Rules
 */
class Length extends BaseRule
{
    protected $message = ':attribute的长度需为%d个字节';

    protected $size;

    public function __construct(int $size)
    {
        $this->size         = $size;
        $this->messageParam = [$size];
    }

    public function passes($attribute, $value): bool
    {
        return strlen($value) === $this->size;
    }
}
