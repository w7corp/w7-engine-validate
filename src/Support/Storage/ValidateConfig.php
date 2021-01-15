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

namespace W7\Validate\Support\Storage;

use W7\Core\Helper\Traiter\InstanceTraiter;

class ValidateConfig
{
	use InstanceTraiter;
	
	/**
	 * 自定义规则命名空间前缀
	 * @var array
	 */
	protected array $rulesPath = [];

	/**
	 * 自动加载验证器规则
	 * @var array
	 */
	protected array $autoValidatePath = [];

	/**
	 * 验证器具体关联
	 * @var array
	 */
	protected array $validateLink = [];

	/**
	 * 设置自动加载验证器规则
	 * @param string $controllerPath 控制器路径
	 * @param string $validatePath   验证器路径
	 * @return $this
	 */
	public function setAutoValidatePath(string $controllerPath, string $validatePath): ValidateConfig
	{
		$this->autoValidatePath[$controllerPath] = $validatePath;
		return $this;
	}

	/**
	 * 设置验证器关联
	 * @param string|string[] $controller <p>控制器完整命名空间</p>
	 *                                    如需指定方法，请传数组，第二个元素为方法名
	 * @param string|string[] $validate   <p>验证器完整命名空间</p>
	 *                                    如需指定场景，请传数组，第二个元素为场景名
	 * @return $this
	 */
	public function setValidateLink($controller, $validate):ValidateConfig
	{
		if (is_array($controller)) {
			$controllers = $controller;
			$controller  = $controllers[0];
			$method      = $controllers[1];
			# 数组中不可以存在 “\” 符号
			$controller = md5($controller);
			if (count($controllers) >= 2) {
				if (isset($this->validateLink[$controller])) {
					$_validate = $this->validateLink[$controller];
					$_validate = array_merge($_validate, [
						$method => $validate
					]);
					$this->validateLink[$controller] = $_validate;
				} else {
					$this->validateLink[$controller] = [
						$method => $validate
					];
				}
			}
		} else {
			$controller = md5($controller);
			if (isset($this->validateLink[$controller])) {
				$this->validateLink[$controller]['!__other__'] = $validate;
			} else {
				$this->validateLink[$controller] = [
					'!__other__' => $validate
				];
			}
		}
		return $this;
	}

	/**
	 * 获取验证器具体关联
	 * @param string|null $controller 验证器完整命名空间
	 * @return array
	 */
	public function getValidateLink(?string $controller = null): array
	{
		if (null === $controller) {
			return $this->validateLink;
		}
		return $this->validateLink[md5($controller)] ?? [];
	}

	/**
	 * 设置自定义规则命名空间前缀,<b>如设置多个则全部生效</b>
	 * @param string $rulesPath 自定义规则命名空间前缀
	 * @return $this
	 */
	public function setRulesPath(string $rulesPath): ValidateConfig
	{
		$this->rulesPath[] = $rulesPath;
		$this->rulesPath   = array_unique($this->rulesPath);
		return $this;
	}

	/**
	 * 获取自定义规则命名空间前缀
	 * @return array
	 */
	public function getRulePath(): array
	{
		return $this->rulesPath;
	}

	/**
	 * 获取自动加载验证器规则
	 * @return array
	 */
	public function getAutoValidatePath(): array
	{
		return $this->autoValidatePath;
	}
}
