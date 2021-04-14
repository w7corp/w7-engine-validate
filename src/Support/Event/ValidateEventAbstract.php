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

namespace W7\Validate\Support\Event;

use Closure;

abstract class ValidateEventAbstract implements ValidateEventInterface
{
    /**
     * 当前验证场景
     * @var ?string
     */
    protected ?string $sceneName;

    /**
     * 场景验证前
     * @param array $data 用户输入的数据
     * @param Closure $next
     * @return mixed
     */
    public function beforeValidate(array $data, Closure $next)
    {
        return $next($data);
    }
    
    /**
     * 场景验证后
     * @param array $data 验证后的数据
     * @param Closure $next
     * @return mixed
     */
    public function afterValidate(array $data, Closure $next)
    {
        return $next($data);
    }

    /**
     * 写入当前的验证场景名称
     * @param string|null $sceneName
     * @return $this
     */
    final public function setSceneName(?string $sceneName): ValidateEventAbstract
    {
        $this->sceneName = $sceneName;
        return $this;
    }
}
