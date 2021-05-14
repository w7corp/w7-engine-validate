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

namespace W7\Validate\Support\Storage;

use ArrayAccess;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\HigherOrderWhenProxy;
use Illuminate\Support\Str;

class ValidateCollection extends Collection
{
    /**
     * Determine if an item exists in the collection by key.
     *
     * @param mixed $key
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
     * Execute when the specified field exists
     *
     * @param mixed          $key       Fields to be validated
     * @param callable       $callback  Methods of execution
     * @param callable|null  $default   Methods to execute when not present
     * @return HigherOrderWhenProxy|mixed|ValidateCollection
     */
    public function whenHas($key, callable $callback, callable $default = null)
    {
        return $this->when($this->has($key), $callback, $default);
    }

    /**
     * Execute when the specified field does not exist
     *
     * @param mixed           $key       Fields to be validated
     * @param callable        $callback  Methods to execute when not present
     * @param callable|null   $default   Methods of execution
     * @return HigherOrderWhenProxy|mixed|ValidateCollection
     */
    public function whenNotHas($key, callable $callback, callable $default = null)
    {
        return $this->when(!$this->has($key), $callback, $default);
    }

    /**
     * Get an item from the collection by key.
     *
     * @param mixed              $key     Field Name
     * @param mixed|Closure|null $default Default Value
     * @return array|ArrayAccess|mixed
     */
    public function get($key, $default = null)
    {
        if (false !== strpos($key, '*')) {
            $explicitPath = rtrim(explode('*', $key)[0], '.') ?: null;
            $results      = [];
            $_default     = rand(1e+5, 1e+10) . time();
            $_value       = Arr::get($this->items, $explicitPath, $_default);

            if ($_default !== $_value) {
                Arr::set($results, $explicitPath, $_value);
            }

            if (! Str::contains($key, '*') || Str::endsWith($key, '*')) {
                $value = Arr::get($this->items, $key);
            } else {
                data_set($results, $key, null, true);

                $results = Arr::dot($results);

                $keys = [];

                $pattern = str_replace('\*', '[^\.]+', preg_quote($key));

                foreach ($results as $_key => $_value) {
                    if (preg_match('/^' . $pattern . '/', $_key, $matches)) {
                        $keys[] = $matches[0];
                    }
                }

                $value = [];
                $keys  = array_unique($keys);

                foreach ($keys as $key) {
                    $value[] = Arr::get($this->items, $key);
                }
            }

            $value = $value ?: value($default);
        } elseif (false !== strpos($key, '.')) {
            $value = Arr::get($this->items, $key, $default);
        } else {
            $value = parent::get($key, $default);
        }

        return $value;
    }

    /**
     * Write the specified value in the collection
     *
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value): ValidateCollection
    {
        Arr::set($this->items, $key, $value);
        return $this;
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->items)) {
            return $this->offsetGet($key);
        }

        return parent::__get($key);
    }

    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }
}
