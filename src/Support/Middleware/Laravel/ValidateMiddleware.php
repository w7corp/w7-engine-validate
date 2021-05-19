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

namespace W7\Validate\Support\Middleware\Laravel;

use Closure;
use Illuminate\Http\Request;
use W7\Validate\Support\Storage\ValidateMiddlewareConfig;

class ValidateMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        list($controller, $scene) = explode('@', $request->route()->getActionName());

        $validator = ValidateMiddlewareConfig::instance()->getValidateFactory()->getValidate($controller, $scene);

        if ($validator) {
            $data = $validator->check($request->all());
            $request->offsetSet('__validate__data__', $data);
        }

        return $next($request);
    }
}
