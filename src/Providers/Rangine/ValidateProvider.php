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

use Itwmw\Validation\Factory;
use W7\Core\Provider\ProviderAbstract;
use W7\Facade\Config;
use W7\Facade\Container;
use W7\Validate\Providers\Laravel\PresenceVerifier;
use W7\Validate\Support\Storage\ValidateConfig;

class ValidateProvider extends ProviderAbstract
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function boot()
    {
        $factory = new Factory(null, str_replace('-', '_', Config::get('app.setting.lang', 'zh_cn')));
        $factory->setPresenceVerifier(new PresenceVerifier(Container::get('db-factory')));
        ValidateConfig::instance()->setFactory($factory);
    }
}
