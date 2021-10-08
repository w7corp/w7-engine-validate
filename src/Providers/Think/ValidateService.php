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

namespace W7\Validate\Providers\Think;

use Itwmw\Validation\Factory;
use think\facade\Config;
use think\Service;
use W7\Validate\Support\Storage\ValidateConfig;

class ValidateService extends Service
{
    public function register()
    {
        $factory = new Factory(null, str_replace('-', '_', Config::get('lang.default_lang')));
        $factory->setPresenceVerifier(new PresenceVerifier());
        ValidateConfig::instance()->setFactory($factory);
    }
}
