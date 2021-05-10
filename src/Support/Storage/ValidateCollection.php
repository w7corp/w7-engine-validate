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
use W7\Validate\Exception\CollectionException;

class ValidateCollection extends Collection
{
    /**
     * @var string|null
     */
    private $paramType;

    /**
     * 将下一次取出的值强制转为int类型
     *
     * @see get 获取指定字段的值
     * @see pop 移除并返回集合的最后一个集合项
     * @see pull 移除并返回集合的第一个集合项
     * @see shift 移除并返回集合的第一个集合项
     *
     * <p>此类型转换对上诉方法有效</p>
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
     * @see get 获取指定字段的值
     * @see pop 移除并返回集合的最后一个集合项
     * @see pull 移除并返回集合的第一个集合项
     * @see shift 移除并返回集合的第一个集合项
     *
     * <p>此类型转换对上诉方法有效</p>
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
     * @see get 获取指定字段的值
     * @see pop 移除并返回集合的最后一个集合项
     * @see pull 移除并返回集合的第一个集合项
     * @see shift 移除并返回集合的第一个集合项
     *
     * <p>此类型转换对上诉方法有效</p>
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
     * @see get 获取指定字段的值
     * @see pop 移除并返回集合的最后一个集合项
     * @see pull 移除并返回集合的第一个集合项
     * @see shift 移除并返回集合的第一个集合项
     *
     * <p>此类型转换对上诉方法有效</p>
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
     * @see get 获取指定字段的值
     * @see pop 移除并返回集合的最后一个集合项
     * @see pull 移除并返回集合的第一个集合项
     * @see shift 移除并返回集合的第一个集合项
     *
     * <p>此类型转换对上诉方法有效</p>
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
     * @see get 获取指定字段的值
     * @see pop 移除并返回集合的最后一个集合项
     * @see pull 移除并返回集合的第一个集合项
     * @see shift 移除并返回集合的第一个集合项
     *
     * <p>此类型转换对上诉方法有效</p>
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
     * @param mixed              $key     字段名称
     * @param mixed|Closure|null $default 默认值
     * @return array|ArrayAccess|mixed
     * @throws CollectionException
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

        return $this->typeConversion($value);
    }

    /**
     * 移除并返回集合的最后一个集合项
     * @return mixed
     * @throws CollectionException
     */
    public function pop()
    {
        return $this->typeConversion(parent::pop());
    }

    /**
     * 移除并返回集合的第一个集合项
     * @return mixed
     * @throws CollectionException
     */
    public function shift()
    {
        return $this->typeConversion(parent::shift());
    }

    /**
     * 将指定键对应的值从集合中移除并返回
     * @param mixed $key     字段名称
     * @param null $default  默认值
     * @return mixed
     * @throws CollectionException
     */
    public function pull($key, $default = null)
    {
        $value = parent::pull($key, $default);
        return $this->typeConversion($value);
    }

    /**
     * 将值转为指定类型
     * @param $value
     * @return mixed
     * @throws CollectionException
     */
    private function typeConversion($value)
    {
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
