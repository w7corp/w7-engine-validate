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

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use PHPUnit\Framework\TestCase;
use W7\Validate\Support\Storage\ValidateConfig;

class BaseTestValidate extends TestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $langPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'laravel-lang' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR;
        if (file_exists($langPath . 'locales')) {
            $langPath .= 'locales';
        } else {
            $langPath .= 'src';
        }

        $loader     = new FileLoader(new Filesystem(), $langPath);
        $translator = new Translator($loader, 'zh_CN');

        ValidateConfig::instance()->setTranslator($translator);
        ValidateConfig::instance()->setRulesPath('W7\\Tests\\Material\\Rules\\');
        
        parent::__construct($name, $data, $dataName);
    }
}