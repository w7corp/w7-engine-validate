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
 * 通过字节的方式来限定长度的合法范围
 * @package W7\App\Model\Validate\Rules
 */
class LengthBetween extends BaseRule
{
    protected $message = ':attribute的长度需为%d~%d字节之间';

    protected $min;

    protected $max;

    public function __construct(int $min, int $max)
    {
        $this->min          = $min;
        $this->max          = $max;
        $this->messageParam = [$min, $max];
    }

    public function passes($attribute, $value): bool
    {
        return strlen($value) >= $this->min && strlen($value) <= $this->max;
    }
}
