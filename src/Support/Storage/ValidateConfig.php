<?php

namespace W7\Validate\Support\Storage;

use Exception;
use W7\Core\Helper\Traiter\InstanceTraiter;

/**
 * Class ValidateConfig
 * @package W7\Validate\Support\Storage
 * @method ValidateConfig rulesPath(string $path)
 * @method ValidateConfig controllerPath(string $path)
 * @method ValidateConfig validatePath(string $path)
 * @property-read string $rulesPath
 * @property-read string $controllerPath
 * @property-read string $validatePath
 */
class ValidateConfig
{
	use InstanceTraiter;
	
	/**
	 * 自定义规则命名空间前缀
	 * @var string
	 */
	protected $rulesPath = '';
	
	/** @var string  */
	protected $controllerPath = null;
	
	/** @var string  */
	protected $validatePath = null;
	
	public function __get($name)
	{
		if (false === property_exists($this, $name)) {
			throw new Exception('Unknown property:' . $name);
		}
		
		return $this->$name;
	}
	
	public function __call($name, $args)
	{
		if (false === property_exists($this, $name)) {
			throw new Exception('Unknown property: ' . $name);
		}
		
		$this->$name = $args[0];
		return $this;
	}
	
	public function __clone()
	{
	}
}
