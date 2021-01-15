<?php

/**
 * WeEngine System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Validate\Support\Storage;

use ArrayAccess;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\HigherOrderWhenProxy;

class ValidateCollection extends Collection
{
	/**
	 * 判断集合中是否存在指定的字段
	 * @param mixed $key 要验证的字段
	 * @return bool
	 */
	public function has($key): bool
	{
		$keys = is_array($key) ? $key : func_get_args();
		
		foreach ($keys as $value) {
			if (!Arr::has($this->items, $value)) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * 当指定字段存在时执行
	 * @param mixed          $key       要验证的字段
	 * @param callable       $callback  存在时执行
	 * @param callable|null  $default   不存在时执行
	 * @return HigherOrderWhenProxy|mixed|ValidateCollection
	 */
	public function whenHas($key, callable $callback, callable $default = null)
	{
		return $this->when($this->has($key), $callback, $default);
	}

	/**
	 * 当指定字段不存在时执行
	 * @param mixed           $key       要验证的字段
	 * @param callable        $callback  不存在时执行
	 * @param callable|null   $default   存在时执行
	 * @return HigherOrderWhenProxy|mixed|ValidateCollection
	 */
	public function whenNotHas($key, callable $callback, callable $default = null)
	{
		return $this->when(!$this->has($key), $callback, $default);
	}

	/**
	 * 获取指定字段的值
	 * @param mixed $key     字段名称
	 * @param null  $default 默认值
	 * @return array|ArrayAccess|mixed
	 */
	public function get($key, $default = null)
	{
		if (false !== strpos($key, '.')) {
			return Arr::get($this->items, $key, $default instanceof Closure ? $default() : $default);
		}
		return parent::get($key, $default);
	}

	/**
	 * 在集合中写入指定的值
	 * @param mixed $key   要写入的字段
	 * @param mixed $value 要写入的值
	 * @return $this
	 */
	public function set($key, $value): ValidateCollection
	{
		Arr::set($this->items, $key, $value);
		return $this;
	}
}
