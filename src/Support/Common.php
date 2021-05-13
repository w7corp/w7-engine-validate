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

namespace W7\Validate\Support;

class Common
{
    /**
     * Get the name and parameters of the rule
     *
     * @param string $value  Complete Rules
     * @param bool $parsing  Whether to parse parameters, default is false
     * @return array
     */
    public static function getKeyAndParam(string $value, bool $parsing = false): array
    {
        $param = '';
        if (false !== strpos($value, ':')) {
            $arg = explode(':', $value, 2);
            $key = $arg[0];
            if (count($arg) >= 2) {
                $param = $arg[1];
            }
        } else {
            $key = $value;
        }

        if ($parsing) {
            $param = explode(',', $param);
        }
        return [$key, $param];
    }

    /**
     * Name of the generated error message
     *
     * @param string $fieldName
     * @param string $rule
     * @return string
     */
    public static function makeMessageName(string $fieldName, string $rule): string
    {
        if (false !== strpos($rule, ':')) {
            $rule = substr($rule, 0, strpos($rule, ':'));
        }
        return $fieldName . '.' . $rule;
    }
}
