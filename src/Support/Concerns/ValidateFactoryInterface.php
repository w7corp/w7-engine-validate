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

namespace W7\Validate\Support\Concerns;

use W7\Validate\Validate;

interface ValidateFactoryInterface
{
    /**
     * 根据控制器获取验证器
     * @param string $controller
     * @param string $scene
     * @return false|Validate
     */
    public function getValidate(string $controller, string $scene = '');
}
