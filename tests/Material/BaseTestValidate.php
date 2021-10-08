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

use PHPUnit\Framework\TestCase;
use W7\Validate\Support\Storage\ValidateConfig;
use Itwmw\Validation\Factory;

class BaseTestValidate extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $factory = new Factory();
        ValidateConfig::instance()->setFactory($factory)
            ->setRulesPath('W7\\Tests\\Material\\Rules\\');
        
        parent::__construct($name, $data, $dataName);
    }
}
