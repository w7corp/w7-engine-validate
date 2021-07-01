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

namespace W7\Tests\Material;

use PHPUnit\Framework\Assert;

class Count
{
    private static $count;

    public static function incremental(string $name)
    {
        self::$count[$name] = (self::$count[$name] ?? 0) + 1;
    }

    public static function decrease(string $name)
    {
        self::$count[$name] = (self::$count[$name] ?? 0) - 1;
    }

    public static function reset(string $name)
    {
        self::$count[$name] = 0;
    }

    public static function value(string $name, int $value = null)
    {
        if (null === $value) {
            return self::$count[$name] ?? 0;
        }

        self::$count[$name] = $value;
        return $value;
    }

    public static function __callStatic($name, $arguments)
    {
        return self::value($name, ...$arguments);
    }

    public static function assertEquals($expected, string $name, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false)
    {
        $actual = static::value($name);
        Assert::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }
}
