<?php

namespace W7\Validate;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use LogicException;
use Psr\Http\Message\RequestInterface;
use W7\Core\Facades\Context;
use W7\Core\Facades\Validator;
use W7\Validate\Exception\ValidateException;
use W7\Validate\Support\Event\ValidateResult;
use W7\Validate\Support\Rule\BaseRule;
use W7\Validate\Support\Storage\ValidateConfig;
use W7\Validate\Support\Storage\ValidateHandler;

class Validate
{
	/**
	 * 自定义错误消息
	 * @var array
	 */
	protected $message = [];

	/**
	 * 验证规则
	 * @var array
	 */
	protected $rule = [];

	/**
	 * 验证场景数据，key为控制器内的方法
	 * @var array
	 */
	protected $scene = [];

	/** @var array  */
	protected $customAttributes = [];

	/**
	 * 当前验证场景
	 * @var string
	 */
	private $currentScene = null;

	/**
	 * 验证的规则
	 * @var Collection
	 */
	private $checkRule;

	/**
	 * sometimes验证
	 * @var array
	 */
	private $sometimes = [];

	/**
	 * 扩展方法名
	 * @var array
	 */
	private static $extendName = [];

	/**
	 * 验证器事件处理类
	 * @var array
	 */
	private $handlers = [];
	
	/**
	 * Request请求实例
	 * @var RequestInterface|null
	 */
	protected $request = null;
	
	public function __construct(RequestInterface $request = null)
	{
		$this->request = $request;
	}
	
	/**
	 * 自动验证
	 * @param array $data
	 * @return array 返回验证成功后的数据
	 * @throws ValidateException
	 */
	public function check(array $data)
	{
		try {
			$data = $this->handleEvent($data, 'beforeValidate');
			/** @var \Illuminate\Validation\Validator $v */
			$v = Validator::make($data, $this->getSceneRules(), $this->message, $this->customAttributes);
			if (!empty($this->sometimes)) {
				foreach ($this->sometimes as $sometime) {
					$v->sometimes(...$sometime);
				}
			}
			$data = $this->handleEvent($v->validate(), 'afterValidate');
			return $data;
		} catch (ValidationException $e) {
			$errors       = $e->errors();
			$errorMessage = '';
			foreach ($errors as $field => $message) {
				$errorMessage = $message[0];
				break;
			}

			throw new ValidateException($errorMessage, 403, $errors);
		}
	}

	private function handleEvent(array $data, string $method)
	{
		$request = $this->request ?: Context::getRequest();
		$result  = (new ValidateHandler($data, $this->handlers ?: [], $request))->handle($method);
		if (is_string($result)) {
			throw new ValidateException($result, 403);
		} elseif ($result instanceof ValidateResult) {
			$this->request = $result->getRequest();
			return $result->getData();
		}
		
		throw new LogicException('Validate event return type error');
	}
	
	public function getRequest()
	{
		return $this->request;
	}

	private function init()
	{
		$this->checkRule = [];
		$this->handlers  = [];
	}

	private function getSceneRules()
	{
		$this->init();
		$this->getScene($this->currentScene);
		$this->checkRule->transform(function ($rule, $field) {
			if (!is_array($rule) && !$rule instanceof Collection) {
				$_rules = collect(explode('|', $rule));
			} else {
				$_rules = collect($rule);
			}
			$_rules->transform(function ($value) use ($field) {
				if (is_string($value)) {
					# 判断是否为自定义规则
					$ruleClass = $this->getRuleClass($value);
					if (false !== $ruleClass) {
						# 给自定义规则设置自定义错误消息
						$message = $this->getMessages($field, $value);
						if ($message) {
							$ruleClass->setMessage($message);
						}
						return $ruleClass;
					}
					return $this->getExtendsName($value, $field);
				}
				return $value;
			});
			return $_rules;
		});
		return $this->checkRule->toArray();
	}

	private function getExtendsName(string $rule, string $field = null)
	{
		# 取回真实的自定义规则方法名称，以及修改对应的错误消息
		if (in_array($rule, self::$extendName)) {
			$ruleName = md5(get_called_class() . $rule);
			# 判断是否为自定义规则方法定义了错误消息
			if (null !== $field && isset($this->message[$field . '.' . $rule])) {
				$this->message[$ruleName] = $this->message[$field . '.' . $rule];
			}
			return $ruleName;
		}
		return $rule;
	}

	private function makeMessageName(string $field, string $rule)
	{
		if (false !== strpos($rule, ':')) {
			$rule = substr($rule, 0, strpos($rule, ':'));
		}
		return $field . '.' . $rule;
	}

	private function getScene(string $name = null)
	{
		$this->sometimes = [];

		if (empty($name)) {
			$this->checkRule = collect($this->rule);
			return $this;
		}
		# 判断自定义验证场景是否存在
		if (method_exists($this, 'scene' . ucfirst($name))) {
			$this->checkRule = collect($this->rule);
			call_user_func([$this, 'scene' . ucfirst($name)]);
		} elseif (isset($this->scene[$name])) { // 判断验证场景是否存在
			$sceneRule = $this->scene[$name];
			if (isset($sceneRule['use']) && !empty($sceneRule['use'])) { // 判断验证场景是否指定了其他验证场景
				$this->getScene($sceneRule['use']);
			} else {
				$this->checkRule = collect($this->rule)->only($sceneRule);
			}

			# 判断是否定义了事件
			if (isset($sceneRule['handler'])) {
				$handler = $sceneRule['handler'];
				if (is_string($handler)) {
					$this->handlers[] = [$handler,[]];
				} else {
					$handlerClass = array_shift($handler);
					$this->handler($handlerClass, ...$handler);
				}
			}
		} else {
			# 如果验证场景找不到，则默认验证全部规则
			$this->checkRule = collect($this->rule);
		}

		return $this;
	}

	private function getRuleClass(string $ruleName)
	{
		$ruleNameSpace = ValidateConfig::instance()->rulesPath . ucfirst($ruleName);
		if (class_exists($ruleNameSpace)) {
			return new $ruleNameSpace();
		}
		return false;
	}
	
	/**
	 * 注册自定义验证方法
	 *
	 * @param string $rule 规则名称
	 * @param Closure|string $extension 闭包规则，回调四个值 $attribute, $value, $parameters, $validator
	 * @param string|null $message 错误消息
	 */
	public static function extend(string $rule, $extension, string $message = null)
	{
		array_push(self::$extendName, $rule);
		self::$extendName = array_unique(self::$extendName);

		# 多个验证器使用了同样的rule会导致后面的方法无法生效
		# 故这里根据命名空间生成一个独一无二的rule名称
		$ruleName = md5(get_called_class() . $rule);
		Validator::extend($ruleName, $extension, $message);
	}

	/**
	 * 设置验证场景
	 *
	 * @param string $name
	 * @return $this
	 */
	public function scene(string $name)
	{
		$this->currentScene = $name;
		return $this;
	}
	
	/**
	 * @param string $handler
	 * @param mixed ...$params
	 * @return $this
	 */
	public function handler(string $handler, ...$params)
	{
		$this->handlers[] = [$handler,$params];
		return $this;
	}

	/**
	 * 指定需要验证的字段列表
	 *
	 * @param array $fields 字段名
	 * @return $this
	 */
	public function only(array $fields)
	{
		$this->checkRule = $this->checkRule->only($fields);
		return $this;
	}

	/**
	 * 追加某个字段的验证规则
	 *
	 * @param string $field 字段名
	 * @param string $rule 验证规则
	 * @return $this
	 */
	public function append(string $field, string $rule)
	{
		if (isset($this->checkRule[$field])) {
			if ($this->checkRule[$field] instanceof Collection) {
				$appendRule = $rule;
				if (!is_array($appendRule)) {
					$appendRule = explode('|', $appendRule);
				}
				$this->checkRule[$field] = $this->checkRule[$field]->concat($appendRule);
			} else {
				$this->checkRule[$field] = $this->checkRule[$field] . '|' . $rule;
			}
		}

		return $this;
	}

	/**
	 * 移除某个字段的验证规则
	 *
	 * @param string $field 字段名
	 * @param string|null $rule 验证规则 null 移除所有规则
	 * @return $this
	 */
	public function remove(string $field, string $rule = null)
	{
		if (isset($this->checkRule[$field])) {
			if (null === $rule) {
				unset($this->checkRule[$field]);
			} else {
				$rules = $this->checkRule[$field];
				if (!is_array($rules)) {
					$rules = explode('|', $rules);
				}
				$rules                   = collect($rules);
				$this->checkRule[$field] = $rules->diff(explode('|', $rule));
			}
		}
		return $this;
	}

	/**
	 * 复杂条件验证
	 *
	 * @param string|string[] $attribute 字段
	 * @param string|array|BaseRule $rules 规则
	 * @param callable $callback 回调方法,返回true规则生效
	 * @return $this
	 */
	public function sometimes($attribute, $rules, callable $callback)
	{
		if (is_string($rules)) {
			$rules = collect(explode('|', $rules));
		} elseif (is_array($rules)) {
			$rules = collect($rules);
		}

		if ($rules instanceof Collection) {
			$rules->transform(function ($rule) use ($attribute) {
				if (is_string($rule)) {
					$ruleClass = $this->getRuleClass($rule);
					if (false !== $ruleClass) {
						if (is_array($attribute) && !empty($attribute)) {
							$attr = $attribute[0];
						} else {
							$attr = $attribute;
						}
						$message = $this->getMessages($attr, $rule);
						if (false !== $message) {
							$ruleClass->setMessage($message);
						}
						return $ruleClass;
					}
				}
				return $rule;
			});
			$rules = $rules->toArray();
		}

		$this->sometimes[] = [$attribute,$rules,$callback];
		return $this;
	}

	/**
	 * 获取验证规则
	 *
	 * @param null $rule 验证字段
	 * @return array|mixed|null
	 */
	public function getRules($rule = null)
	{
		if (null === $rule) {
			return $this->rule;
		}

		if (is_array($rule)) {
			return collect($this->rule)->only($rule)->toArray();
		} else {
			return $this->rule[$rule] ?? null;
		}
	}

	/**
	 * 设置验证规则(叠加)
	 *
	 * @param array|null $rules [字段 => 规则] 如果$rules为null，则清空全部验证规则
	 * @return $this
	 */
	public function setRules(array $rules = null)
	{
		if (null === $rules) {
			$this->rule = [];
		}
		$this->rule = array_merge($this->rule, $rules);
		return $this;
	}
	/**
	 * 获取验证消息
	 *
	 * @param null|string|string[] $key 完整消息Key值
	 * @param string|null $rule 如果第一个值为字段名，则第二个值则为规则，否则请留空
	 * @return array|mixed|null
	 */
	public function getMessages($key = null, string $rule = null)
	{
		if (null === $key) {
			return $this->message;
		}

		if (null !== $rule) {
			$messageName = $this->makeMessageName($key, $rule);
		} else {
			$messageName = $key;
		}

		if (is_array($messageName)) {
			return collect($this->message)->only($messageName)->toArray();
		} else {
			return $this->message[$messageName] ?? null;
		}
	}

	/**
	 * 设置错误消息(叠加)
	 *
	 * @param array|null $message [字段.规则 => 验证消息] 如果$message为null，则清空全部验证消息
	 * @return $this
	 */
	public function setMessages(array $message = null)
	{
		if (null === $message) {
			$this->message = [];
		}

		$this->message = array_merge($this->message, $message);
		return $this;
	}

	/**
	 * 设置验证场景(叠加)
	 *
	 * @param array|null $scene [场景 => [字段]] 如果$scene为null，则清空全部验证场景
	 * @return $this
	 */
	public function setScene(array $scene = null)
	{
		if (null === $scene) {
			$this->scene = [];
		}

		$this->scene = array_merge($this->scene, $scene);
		return $this;
	}
}
