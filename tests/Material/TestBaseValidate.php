<?php

/**
 * WeEngine System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Tests\Material;

use PHPUnit\Framework\TestCase;
use W7\Validate\Support\Storage\ValidateConfig;

class TestBaseValidate extends TestCase
{
	public function __construct($name = null, array $data = [], $dataName = '')
	{
		$this->rangineInit();
		ValidateConfig::instance()->setRulesPath('W7\\Tests\\Material\\Rules\\');
		
		parent::__construct($name, $data, $dataName);
	}
	
	private function rangineInit()
	{
		!defined('BASE_PATH')    && define('BASE_PATH', dirname(__DIR__, 2));
		!defined('APP_PATH')     && define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'app');
		!defined('RUNTIME_PATH') && define('RUNTIME_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'runtime');
	}
}
