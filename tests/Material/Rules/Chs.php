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

class Chs extends BaseRule
{
    /**
     * 默认错误消息
     * @var string
     */
    protected $message = ':attribute的值只能具有中文';
    
    /**
     * 确定验证规则是否通过。
     *
     * @param mixed $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return is_scalar($value) && 1 === preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', (string)$value);
    }
}
