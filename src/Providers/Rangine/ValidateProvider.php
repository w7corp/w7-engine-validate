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

namespace W7\Validate\Providers\Rangine;

use W7\Core\Provider\ProviderAbstract;
use W7\Facade\Container;
use W7\Validate\Support\Storage\ValidateConfig;

class ValidateProvider extends ProviderAbstract
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        ValidateConfig::instance()->setFactory(Container::get("W7\Contract\Validation\ValidatorFactoryInterface"));
    }
}
