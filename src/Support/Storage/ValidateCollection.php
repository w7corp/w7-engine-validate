<?php

namespace W7\Validate\Support\Storage;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ValidateCollection extends Collection
{
	public function has($key)
	{
		$keys = is_array($key) ? $key : func_get_args();
		
		foreach ($keys as $value) {
			if (!Arr::has($this->items, $value)) {
				return false;
			}
		}
		
		return true;
	}
	
	public function get($key, $default = null)
	{
		if (false !== strpos($key, '.')) {
			return Arr::get($this->items, $key, $default instanceof \Closure ? $default() : $default);
		}
		return parent::get($key, $default);
	}
	
	public function set($key, $value)
	{
		Arr::set($this->items, $key, $value);
		return $this;
	}
}
