<?php

namespace W7\Validate\Support\Storage;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use W7\Validate\Validate;

class ValidateRelation
{
	/**
	 * 验证器指定 控制器 => 验证器
	 * @var string[]
	 */
	protected $validate = [

	];
	
	/**
	 * 验证场景指定 验证器 => [方法 => 场景]
	 * @var string[][]
	 */
	protected $scene = [

	];
	
	final public function getValidate(ServerRequestInterface $request)
	{
		$route      = $request->getAttribute('route');
		$controller = $route['controller'] ?? '';
		$method     = $route['method']     ?? '';
		$validate   = null;
		$scene      = null;
		
		# 指定了验证器
		if (isset($this->validate[$controller])) {
			$validate = $this->validate[$controller];
		}
		# 指定了验证场景
		if (isset($this->scene[$controller])) {
			$scene = $this->scene[$controller];
			if (isset($scene[$method])) {
				$scene = $scene[$method];
				if (is_array($scene) && 2 === count($scene)) {
					$validate = $scene[0];
					$scene    = $scene[1];
				}
			}
		}
		
		# 默认场景名为方法名
		if (empty($scene)) {
			$scene = $method;
		}
		
		# 取默认的验证器
		if (empty($validate)) {
			$validate   = str_replace(ValidateConfig::instance()->collectionPath, '', $controller);
			$_namespace = explode('\\', $validate);
			$fileName   = str_replace('Controller', 'Validate', array_pop($_namespace));
			$validate   = ValidateConfig::instance()->validatePath . implode('\\', $_namespace) . '\\' . $fileName;
		}
		if (class_exists($validate)) {
			if (is_subclass_of($validate, Validate::class)) {
				/** @var Validate $validator */
				$validator = new $validate($request);
				$validator->scene($scene);
				return $validator;
			}
			
			throw new Exception("The given 'Validate' " . $validate . ' has to be a subtype of W7\Validate\Validate');
		}
		return false;
	}
}
