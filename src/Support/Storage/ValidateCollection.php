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

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ValidateCollection extends Collection
{
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

	public function whenHas($key, callable $callback, callable $default = null)
	{
		return $this->when($this->has($key), $callback, $default);
	}

	public function whenNotHas($key, callable $callback, callable $default = null)
	{
		return $this->when(!$this->has($key), $callback, $default);
	}
	
	public function get($key, $default = null)
	{
		if (false !== strpos($key, '.')) {
			return Arr::get($this->items, $key, $default instanceof Closure ? $default() : $default);
		}
		return parent::get($key, $default);
	}
	
	public function set($key, $value): ValidateCollection
	{
		Arr::set($this->items, $key, $value);
		return $this;
	}
}
