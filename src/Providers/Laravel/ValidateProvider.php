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

namespace W7\Validate\Providers\Laravel;

use Illuminate\Support\ServiceProvider;
use W7\Validate\Support\Storage\ValidateConfig;

class ValidateProvider extends ServiceProvider
{
    public function register()
    {
        ValidateConfig::instance()->setFramework(1);
    }
}
