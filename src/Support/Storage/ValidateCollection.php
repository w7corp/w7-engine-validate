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
use W7\Validate\Exception\CollectionException;

class ValidateCollection extends Collection
{
	private ?string $paramType;

	/**
	 * 将下一次取出的值强制转为int类型
	 *
	 * <b>请注意，如果你的值不支持强制转换，会抛出CollectionException异常</b>
	 * @return $this
	 */
	public function int(): ValidateCollection
	{
		$this->paramType = 'int';
		return $this;
	}

	/**
	 * 将下一次取出的值强制转为float类型
	 *
	 * <b>请注意，如果你的值不支持强制转换，会抛出CollectionException异常</b>
	 * @return $this
	 */
	public function float(): ValidateCollection
	{
		$this->paramType = 'float';
		return $this;
	}

	/**
	 * 将下一次取出的值强制转为string类型
	 *
	 * <b>请注意，如果你的值不支持强制转换，会抛出CollectionException异常</b>
	 * @return $this
	 */
	public function string(): ValidateCollection
	{
		$this->paramType = 'string';
		return $this;
	}

	/**
	 * 将下一次取出的值强制转为array类型
	 *
	 * <b>请注意，如果你的值不支持强制转换，会抛出CollectionException异常</b>
	 * @return $this
	 */
	public function array(): ValidateCollection
	{
		$this->paramType = 'array';
		return $this;
	}

	/**
	 * 将下一次取出的值强制转为object类型
	 *
	 * <b>请注意，如果你的值不支持强制转换，会抛出CollectionException异常</b>
	 * @return $this
	 */
	public function object(): ValidateCollection
	{
		$this->paramType = 'object';
		return $this;
	}

	/**
	 * 将下一次取出的值强制转为bool类型
	 *
	 * <b>请注意，如果你的值不支持强制转换，会抛出CollectionException异常</b>
	 * @return $this
	 */
	public function bool(): ValidateCollection
	{
		$this->paramType = 'bool';
		return $this;
	}

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
	 * @param mixed $key    字段名称
	 * @param null $default 默认值
	 * @return array|ArrayAccess|mixed
	 * @throws CollectionException
	 */
	public function get($key, $default = null)
	{
		if (false !== strpos($key, '.')) {
			$value = Arr::get($this->items, $key, $default instanceof Closure ? $default() : $default);
		} else {
			$value = parent::get($key, $default);
		}

		if (!empty($this->paramType)) {
			$error = null;
			set_error_handler(function ($type, $msg) use (&$error) {
				$error = $msg;
			});
			settype($value, $this->paramType);
			restore_error_handler();
			$this->paramType = null;
			if (!empty($error)) {
				throw new CollectionException($error);
			}
		}
		
		return $value;
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
