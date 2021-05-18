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

namespace W7\Validate;

use Closure;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use LogicException;
use W7\Validate\Exception\ValidateException;
use W7\Validate\Support\Rule\BaseRule;
use W7\Validate\Support\Storage\ValidateCollection;
use W7\Validate\Support\Storage\ValidateConfig;
use W7\Validate\Support\Storage\ValidateHandler;

class Validate
{
    /**
     * 自定义错误消息
     * @var array
     */
    protected array $message = [];

    /**
     * 验证规则
     * @var array
     */
    protected array $rule = [];

    /**
     * 验证场景数据，key为控制器内的方法
     * @var array
     */
    protected array $scene = [];

    /**
     * 全局事件处理器
     * @var array
     */
    protected array $handler = [];

    /**
     * 字段名称
     * @var array
     */
    protected array $customAttributes = [];

    /**
     * 是否首次验证失败后停止运行
     * @var bool
     */
    protected bool $bail = true;

    /**
     * 所有验证的字段在存在时不能为空
     * @var bool
     */
    protected bool $filled = true;

    /**
     * 事件优先
     * @var bool
     */
    private bool $eventPriority = true;

    /**
     * 当前验证场景
     * @var ?string
     */
    private ?string $currentScene = null;

    /**
     * 验证的规则
     * @var Collection
     */
    private Collection $checkRule;

    /**
     * 扩展方法名
     * @var array
     */
    private static array $extendName = [];

    /**
     * 隐形扩展方法名
     * @var array
     */
    private static array $implicitRules = [];

    /**
     * 验证器事件处理类
     * @var array
     */
    private array $handlers = [];

    /**
     * 验证前需要执行的方法
     * @var array
     */
    private array $befores = [];

    /**
     * 验证后需要执行的方法
     * @var array
     */
    private array $afters = [];

    /**
     * 当前进行验证的数据
     * @var array
     */
    private array $checkData = [];

    /**
     * 创建一个验证器
     * @param array $rules               验证规则
     * @param array $messages            错误消息
     * @param array $customAttributes    字段名称
     * @return Validate
     */
    public static function make(array $rules = [], array $messages = [], array $customAttributes = []): Validate
    {
        if (empty($rules)) {
            return new static();
        }
        return (new static())->setRules($rules)->setMessages($messages)->setCustomAttributes($customAttributes);
    }

    /**
     * 获取验证器工厂
     * @return Factory
     */
    private static function getValidationFactory(): Factory
    {
        return ValidateConfig::instance()->getFactory();
    }
    
    /**
     * 自动验证
     * @param array $data 待验证的数据
     * @return array
     * @throws ValidateException
     */
    public function check(array $data): array
    {
        try {
            $this->checkData = $data;
            $rule            = $this->getSceneRules();

            if ($this->filled) {
                $rule = $this->addFilledRule($rule);
            }

            if ($this->bail) {
                $rule = $this->addBailRule($rule);
            }

            if ($this->eventPriority) {
                $data = $this->handleEvent($data, 'beforeValidate');
                $data = $this->handleCallback($data, 1);
            } else {
                $data = $this->handleCallback($data, 1);
                $data = $this->handleEvent($data, 'beforeValidate');
            }

            $v    = $this->getValidationFactory()->make($data, $rule, $this->message, $this->customAttributes);
            $data = $v->validate();

            if ($this->eventPriority) {
                $data = $this->handleCallback($data, 2);
                $data = $this->handleEvent($data, 'afterValidate');
            } else {
                $data = $this->handleEvent($data, 'afterValidate');
                $data = $this->handleCallback($data, 2);
            }

            return $data;
        } catch (ValidationException $e) {
            $errors       = $this->handlingError($e->errors());
            $errorMessage = '';
            foreach ($errors as $message) {
                $errorMessage = $message[0];
                break;
            }

            throw new ValidateException($errorMessage, 403, $errors, $e);
        }
    }

    /**
     * 获取当前的验证数据
     * @param string $key
     * @param null $default
     * @return array|mixed
     */
    public function getData(string $key = '', $default = null)
    {
        if (!empty($key)) {
            return Arr::get($this->checkData, $key, $default);
        }
        return $this->checkData;
    }

    /**
     * 获取当前的验证数据,返回验证集合类型
     * @return Support\Storage\ValidateCollection
     */
    public function getValidateData(): Support\Storage\ValidateCollection
    {
        return validate_collect($this->getData());
    }

    /**
     * 处理错误消息
     * @param array $errors
     * @return array
     */
    private function handlingError(array $errors): array
    {
        foreach ($errors as &$errorMessages) {
            if (is_array($errorMessages)) {
                $errorMessages = array_map([$this, 'replacingFieldsInMessage'], $errorMessages);
            } else {
                $errorMessages = $this->replacingFieldsInMessage($errorMessages);
            }
        }
        return $errors;
    }

    /**
     * 替换错误消息中的字段
     * @param string $message
     * @return string|string[]
     */
    private function replacingFieldsInMessage(string $message)
    {
        if (preg_match_all('/{:(.*?)}/', $message, $matches) > 0) {
            foreach ($matches[0] as $index => $pregString) {
                $message = str_replace($pregString, Arr::get($this->checkData, $matches[1][$index], ''), $message);
            }
        }
        return $message;
    }

    /**
     * 处理方法
     * @param array $data
     * @param int $type 类型，1 验证前方法 2 验证后方法
     * @return array
     * @throws ValidateException
     */
    private function handleCallback(array $data, int $type): array
    {
        switch ($type) {
            case 1:
                $callbacks = $this->befores;
                $typeName  = 'before';
                break;
            case 2:
                $callbacks = $this->afters;
                $typeName  = 'after';
                break;
            default:
                throw new LogicException('Type Error');
        }

        if (empty($callbacks)) {
            return $data;
        }

        $callback = array_map(function ($callback) use ($typeName) {
            return function ($data, $next) use ($callback, $typeName) {
                list($callback, $param) = $callback;
                $callback = $typeName . ucfirst($callback);
                if (method_exists($this, $callback)) {
                    return call_user_func([$this, $callback], $data, $next, ...$param);
                }
                throw new LogicException('Method Not Found');
            };
        }, $callbacks);

        $pipeline = array_reduce(
            array_reverse($callback),
            function ($stack, $pipe) {
                return function ($data) use ($stack, $pipe) {
                    return $pipe($data, $stack);
                };
            },
            fn ($data) => $data
        );

        $data = $pipeline($data);

        return $this->handleEventResult($data);
    }

    /**
     * 验证事件处理
     * @param array $data    验证的数据
     * @param string $method 事件名称
     * @return array
     * @throws ValidateException
     */
    private function handleEvent(array $data, string $method): array
    {
        if (empty($this->handlers)) {
            return $data;
        }
        $result = (new ValidateHandler($data, $this->handlers, $this->currentScene))->handle($method);
        return $this->handleEventResult($result);
    }

    /**
     * 处理事件结果
     * @param $result
     * @return array
     * @throws ValidateException
     */
    private function handleEventResult($result): array
    {
        if (is_string($result)) {
            if (isset($this->message[$result])) {
                $result = $this->replacingFieldsInMessage($this->message[$result]);
            }
            throw new ValidateException($result, 403);
        } elseif (is_array($result)) {
            return $result;
        }

        throw new LogicException('Validate event return type error');
    }

    /**
     * 初始化验证
     */
    private function init()
    {
        $this->checkRule     = collect([]);
        $this->handlers      = [];
        $this->afters        = [];
        $this->befores       = [];
        $this->eventPriority = true;
    }

    /**
     * 获取当前场景下需要验证的规则
     * @return array
     * @throws ValidateException
     */
    private function getSceneRules(): array
    {
        $this->init();
        $this->addHandler($this->handler);
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

                    return $this->getExtendsRule($value, $field);
                }
                return $value;
            });
            return $_rules;
        });
        return $this->checkRule->toArray();
    }

    /**
     * 获取扩展规则
     * 由于为了区分多个验证器的相同自定义方法名，对方法名做了处理，此方法为了使规则和处理后的方法名对应上
     * @param string      $rule   规则名称
     * @param string|null $field  字段
     * @return string
     */
    private function getExtendsRule(string $rule, string $field = null): string
    {
        list($rule, $param) = $this->getKeyAndParam($rule, false);

        # 取回真实的自定义规则方法名称，以及修改对应的错误消息
        if (array_key_exists($rule, self::$extendName)) {
            $ruleName = md5(get_called_class() . $rule);
            # 判断是否为自定义规则方法定义了错误消息
            if (null !== $field && isset($this->message[$field . '.' . $rule])) {
                $this->message[$field . '.' . $ruleName] = $this->message[$field . '.' . $rule];
            }

            $rule = $ruleName;
        } else {
            # 如果当前自定义规则中不存在，则判断是否为类方法
            # 如果是类方法，则先注册规则到验证器中，然后再处理对应的错误消息
            if (method_exists($this, 'rule' . ucfirst($rule))) {
                self::extend($rule, Closure::fromCallable([$this, 'rule' . ucfirst($rule)]));

                if ('' !== $param) {
                    $rule = $rule . ':' . $param;
                }

                return $this->getExtendsRule($rule, $field);
            }
        }

        if ('' !== $param) {
            $rule = $rule . ':' . $param;
        }
        return $rule;
    }

    /**
     * 获取规则的名称和参数
     * @param string $value  规则
     * @param bool $parsing  是否解析参数，默认为false
     * @return array
     */
    private function getKeyAndParam(string $value, bool $parsing = false): array
    {
        $param = '';
        if (false !== strpos($value, ':')) {
            $arg = explode(':', $value, 2);
            $key = $arg[0];
            if (count($arg) >= 2) {
                $param = $arg[1];
            }
        } else {
            $key = $value;
        }

        if ($parsing) {
            $param = explode(',', $param);
        }
        return [$key, $param];
    }

    /**
     * 生成错误消息的名称
     * @param string $field 字段
     * @param string $rule  规则
     * @return string
     */
    private function makeMessageName(string $field, string $rule): string
    {
        if (false !== strpos($rule, ':')) {
            $rule = substr($rule, 0, strpos($rule, ':'));
        }
        return $field . '.' . $rule;
    }

    /**
     * 添加事件
     * @param $handlers
     */
    private function addHandler($handlers)
    {
        $this->addCallback(0, $handlers);
    }

    /**
     * 添加验证前方法
     * @param $callback
     */
    private function addBefore($callback)
    {
        $this->addCallback(1, $callback);
    }

    /**
     * 添加验证后方法
     * @param $callback
     */
    private function addAfter($callback)
    {
        $this->addCallback(2, $callback);
    }

    /**
     * 添加方法
     * @param int $intType 0为事件 1为验证前方法 2为验证后方法
     * @param $callback
     */
    private function addCallback(int $intType, $callback)
    {
        switch ($intType) {
            case 0:
                $type = 'handlers';
                break;
            case 1:
                $type = 'befores';
                break;
            case 2:
                $type = 'afters';
                break;
            default:
                throw new LogicException('Type Error');
        }

        if (is_string($callback)) {
            $this->$type[] = [$callback, []];
        } else {
            foreach ($callback as $classOrMethod => $param) {
                if (is_int($classOrMethod)) {
                    $this->$type[] = [$param, []];
                } elseif (is_string($classOrMethod)) {
                    $callbackName = str_replace('s', '', $type);
                    if (is_array($param)) {
                        $this->$callbackName($classOrMethod, ...$param);
                    } else {
                        $this->$callbackName($classOrMethod, $param);
                    }
                }
            }
        }
    }

    /**
     * 添加bail规则
     * @param array $rules 原规则
     * @return array
     */
    private function addBailRule(array $rules): array
    {
        foreach ($rules as &$rule) {
            if (!in_array('bail', $rule)) {
                array_unshift($rule, 'bail');
            }
        }

        return $rules;
    }

    /**
     * 添加filled规则
     * @param array $rules 原规则
     * @return array
     */
    private function addFilledRule(array $rules): array
    {
        $conflictRules = [
            'filled', 'nullable', 'accepted', 'present', 'required', 'required_if', 'required_unless', 'required_with',
            'required_with_all', 'required_without', 'required_without_all',
        ];

        foreach ($rules as &$rule) {
            $rulesName = array_map(function ($value) {
                if (is_object($value)) {
                    // 如果继承了ImplicitRule标记接口
                    // 代表此自定义规则想要在属性为空时执行规则对象
                    // 所以也不需要添加filled规则，那么就让数组对象里存在一个filled
                    // 下面的判断就会自动跳过，实际上规则中没有被添加filled规则
                    if ($value instanceof ImplicitRule) {
                        return 'filled';
                    } else {
                        return '';
                    }
                }

                $ruleName = $this->getKeyAndParam($value)[0];

                // 此处操作同上
                if (in_array($ruleName, self::$implicitRules)) {
                    return 'filled';
                }

                return $ruleName;
            }, $rule);

            if (empty(array_intersect($conflictRules, $rulesName))) {
                array_unshift($rule, 'filled');
            }
        }

        return $rules;
    }

    /**
     * 进入场景
     * @param string|null $name 场景名称
     * @return void
     * @throws ValidateException
     */
    private function getScene(?string $name = null): void
    {
        if (empty($name)) {
            $this->checkRule = collect($this->rule);
            return;
        }
        # 判断自定义验证场景是否存在
        if (method_exists($this, 'scene' . ucfirst($name))) {
            $this->checkRule = collect($this->rule);
            call_user_func([$this, 'scene' . ucfirst($name)]);
        } elseif (isset($this->scene[$name])) {  // 判断验证场景是否存在
            $sceneRule = $this->scene[$name];

            # 判断是否定义了事件
            if (isset($sceneRule['handler'])) {
                $handlers = $sceneRule['handler'];
                $this->addHandler($handlers);
                unset($sceneRule['handler']);
            }

            # 判断是否定义了验证前需要执行的方法
            if (isset($sceneRule['before'])) {
                $callback = $sceneRule['before'];
                $this->addBefore($callback);
                unset($sceneRule['before']);
            }

            # 判断是否定义了验证后需要执行的方法
            if (isset($sceneRule['after'])) {
                $callback = $sceneRule['after'];
                $this->addAfter($callback);
                unset($sceneRule['after']);
            }

            # 判断验证场景是否指定了其他验证场景
            if (isset($sceneRule['use']) && !empty($sceneRule['use'])) {
                $use = $sceneRule['use'];
                if ($use === $name || $use === $this->currentScene) {
                    throw new LogicException('The scene used cannot be the same as the current scene.');
                }
                unset($sceneRule['use']);
                # 如果指定的use是一个方法
                if (method_exists($this, 'use' . ucfirst($use))) {
                    # 进行预验证，将需要传给闭包的值按指定规则进行验证
                    $data = [];
                    if (!empty($sceneRule)) {
                        $randScene = md5(rand(1, 1000000) . time());
                        $data      = (clone $this)->setScene(
                            [$randScene => $sceneRule]
                        )->scene($randScene)->check($this->checkData);
                    }

                    $use = call_user_func([$this, 'use' . ucfirst($use)], $data);
                    if (is_array($use)) {
                        $this->checkRule = collect($this->rule)->only($use);
                        return;
                    }
                }
                $this->getScene($use);
            } else {
                $this->checkRule = collect($this->rule)->only($sceneRule);
            }
        } else {
            # 如果验证场景找不到，则默认验证全部规则
            $this->checkRule = collect($this->rule);
        }
    }

    /**
     * 获取自定义规则的实例类
     *
     * @param string $ruleName      自定义规则名称
     * @param bool   $ruleClassName 是否只获取Class完整命名空间 默认为false
     * @return false|mixed|Closure
     */
    private function getRuleClass(string $ruleName, bool $ruleClassName = false)
    {
        list($ruleName, $param) = $this->getKeyAndParam($ruleName, true);

        foreach (ValidateConfig::instance()->getRulePath() as $rulesPath) {
            $ruleNameSpace = $rulesPath . ucfirst($ruleName);
            if (class_exists($ruleNameSpace) && is_subclass_of($ruleNameSpace, BaseRule::class)) {
                if ($ruleClassName) {
                    return $ruleNameSpace;
                } else {
                    return new $ruleNameSpace(...$param);
                }
            }
        }

        return false;
    }

    /**
     * 注册自定义验证方法
     *
     * @param string               $rule      规则名称
     * @param Closure|string|array $extension 闭包规则，回调四个值 $attribute, $value, $parameters, $validator
     * @param string|null          $message   错误消息
     */
    public static function extend(string $rule, $extension, ?string $message = null)
    {
        self::validatorExtend('', $rule, $extension, $message);
    }

    /**
     * 注册一个自定义的隐式验证器扩展
     *
     * @param string               $rule      规则名称
     * @param Closure|string|array $extension 闭包规则，回调四个值 $attribute, $value, $parameters, $validator
     * @param string|null          $message   错误消息
     */
    public static function extendImplicit(string $rule, $extension, ?string $message = null)
    {
        self::validatorExtend('Implicit', $rule, $extension, $message);
    }

    /**
     * 注册一个自定义依赖性验证器扩展
     *
     * @param string               $rule      规则名称
     * @param Closure|string|array $extension 闭包规则，回调四个值 $attribute, $value, $parameters, $validator
     * @param string|null          $message   错误消息
     */
    public static function extendDependent(string $rule, $extension, ?string $message = null)
    {
        self::validatorExtend('Dependent', $rule, $extension, $message);
    }

    /**
     * 注册自定义验证器扩展
     * @param string               $type      类型
     * @param string               $rule      规则名称
     * @param Closure|string|array $extension 闭包规则，回调四个值 $attribute, $value, $parameters, $validator
     * @param string|null          $message   错误消息
     */
    private static function validatorExtend(string $type, string $rule, $extension, ?string $message = null)
    {
        # 多个验证器使用了同样的rule会导致后面的方法无法生效
        # 故这里根据命名空间生成一个独一无二的rule名称
        $ruleName = md5(get_called_class() . $rule);

        if (array_key_exists($rule, self::$extendName)) {
            array_push(self::$extendName[$rule], $ruleName);
            self::$extendName[$rule] = array_unique(self::$extendName[$rule]);
        } else {
            self::$extendName[$rule] = [$ruleName];
        }

        if (!empty($type)) {
            $method = 'extend' . $type;
        } else {
            $method = 'extend';
        }

        if ('Implicit' === $type) {
            self::$implicitRules[] = $ruleName;
        }

        self::getValidationFactory()->$method($ruleName, $extension, $message);
    }

    /**
     * 注册自定义验证方法错误消息
     * @param string         $rule     规则名称
     * @param string|Closure $replacer 闭包规则，回调四个值  $message,$attribute,$rule,$parameters
     */
    public static function replacer(string $rule, $replacer)
    {
        if (array_key_exists($rule, self::$extendName)) {
            $ruleName = md5(get_called_class() . $rule);
            if (in_array($ruleName, self::$extendName[$rule])) {
                $rule = $ruleName;
            }
        }
        self::getValidationFactory()->replacer($rule, $replacer);
    }

    /**
     * 设置验证场景
     *
     * @param string $name
     * @return $this
     */
    public function scene(string $name): Validate
    {
        $this->currentScene = $name;
        return $this;
    }

    /**
     * 获取当前验证场景名称
     *
     * @return string|null
     */
    public function getCurrentSceneName(): ?string
    {
        return $this->currentScene;
    }

    /**
     * 设置事件优先级
     * @param bool $priority
     * @return $this
     */
    public function setEventPriority(bool $priority): Validate
    {
        $this->eventPriority = $priority;
        return $this;
    }

    /**
     * 加入事件
     * @param string $handler  事件的完整类名，完整命名空间字符串或者加::class
     * @param mixed ...$params 要传递给事件的参数
     * @return $this
     */
    public function handler(string $handler, ...$params): Validate
    {
        $this->handlers[] = [$handler, $params];
        return $this;
    }

    /**
     * 添加一个验证前的需要执行的方法
     * @param string $callbackName 本类的方法名
     * @param mixed  ...$params    要传递给方法的参数
     * @return $this
     */
    public function before(string $callbackName, ...$params): Validate
    {
        $this->befores[] = [$callbackName, $params];
        return $this;
    }

    /**
     * 添加一个验证后需要执行的方法
     * @param string $callbackName 本类的方法名
     * @param mixed  ...$params    要传递给方法的参数
     * @return $this
     */
    public function after(string $callbackName, ...$params): Validate
    {
        $this->afters[] = [$callbackName, $params];
        return $this;
    }

    /**
     * 指定需要验证的字段列表
     *
     * @param array $fields 字段名
     * @return $this
     */
    public function only(array $fields): Validate
    {
        $this->checkRule = $this->checkRule->only($fields);
        return $this;
    }

    /**
     * 添加字段到验证列表中
     *
     * @deprecated
     * @see appendCheckField
     * @param string $field
     * @return $this
     */
    public function addCheckField(string $field): Validate
    {
        $rule            = $this->rule[$field] ?? '';
        $this->checkRule = $this->checkRule->merge([$field => $rule]);
        return $this;
    }

    /**
     * 添加字段到验证列表中
     *
     * @param string $field
     * @return $this
     */
    public function appendCheckField(string $field): Validate
    {
        $rule            = $this->rule[$field] ?? '';
        $this->checkRule = $this->checkRule->merge([$field => $rule]);
        return $this;
    }

    /**
     * 删除验证列表中的字段
     * @param string $field
     * @return $this
     */
    public function removeCheckField(string $field): Validate
    {
        $this->checkRule->forget($field);
        return $this;
    }

    /**
     * 追加某个字段的验证规则
     *
     * @param string $field 字段名
     * @param string $rule 验证规则
     * @return $this
     */
    public function append(string $field, string $rule): Validate
    {
        if (isset($this->checkRule[$field])) {
            if (is_array($this->checkRule[$field])) {
                $this->checkRule[$field] = collect($this->checkRule[$field]);
            }

            if ($this->checkRule[$field] instanceof Collection) {
                $appendRule              = explode('|', $rule);
                $this->checkRule[$field] = $this->checkRule[$field]->concat($appendRule);
            } else {
                if (empty($this->checkRule[$field])) {
                    $this->checkRule[$field] = $rule;
                } else {
                    $this->checkRule[$field] = $this->checkRule[$field] . '|' . $rule;
                }
            }
        }

        return $this;
    }

    /**
     * 移除某个字段的验证规则
     *
     * @param string      $field 字段名
     * @param string|null $rule  验证规则 null 移除所有规则
     * @return $this
     */
    public function remove(string $field, ?string $rule = null): Validate
    {
        $removeRule = $rule;
        if (is_string($rule) && false !== strpos($rule, '|')) {
            $removeRule = explode('|', $rule);
        }

        if (is_array($removeRule)) {
            foreach ($removeRule as $rule) {
                $this->remove($field, $rule);
            }
        } else {
            if (isset($this->checkRule[$field])) {
                if (null === $rule) {
                    $this->checkRule[$field] = [];
                } else {
                    $rules = $this->checkRule[$field];
                    if (is_string($rules)) {
                        $rules = explode('|', $rules);
                        $rules = collect($rules);
                    }

                    if (false !== strpos($rule, ':')) {
                        $rule = substr($rule, 0, strpos($rule, ':'));
                    }
                    $rules = $rules->filter(function ($value) use ($rule) {
                        if (false !== strpos($value, ':')) {
                            $value = substr($value, 0, strpos($value, ':'));
                        }
                        return $value !== $rule;
                    });

                    $this->checkRule[$field] = $rules;
                }
            }
        }

        return $this;
    }

    /**
     * 复杂条件验证
     *
     * @param string|string[]       $attribute 字段
     * @param string|array|BaseRule $rules     规则
     * @param callable              $callback  回调方法,方法提供一个{@see ValidateCollection} $data参数,参数为当前验证的数据,
     *                                         返回true则规则生效
     * @return $this
     */
    public function sometimes($attribute, $rules, callable $callback): Validate
    {
        $data   = $this->getValidateData();
        $result = call_user_func($callback, $data);

        if (!$result) {
            return $this;
        }

        if (is_array($rules)) {
            $rules = implode('|', $rules);
        }

        if (is_array($attribute)) {
            foreach ($attribute as $filed) {
                $this->append($filed, $rules);
            }
        } else {
            $this->append($attribute, $rules);
        }

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
    public function setRules(?array $rules = null): Validate
    {
        if (is_null($rules)) {
            $this->rule = [];
        } else {
            $this->rule = array_merge($this->rule, $rules);
        }

        return $this;
    }

    /**
     * 获取验证消息
     *
     * @param null|string|string[] $key  完整消息Key值
     * @param string|null          $rule 如果第一个值为字段名，则第二个值则为规则，否则请留空
     * @return array|mixed|null
     */
    public function getMessages($key = null, ?string $rule = null)
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
    public function setMessages(?array $message = null): Validate
    {
        if (is_null($message)) {
            $this->message = [];
        } else {
            $this->message = array_merge($this->message, $message);
        }

        return $this;
    }

    /**
     * 设置字段名称(叠加)
     *
     * @param array|null $customAttributes [字段 => 名称] 如果$customAttributes为null，则清空全部字段名称
     * @return $this
     */
    public function setCustomAttributes(?array $customAttributes = null): Validate
    {
        if (is_null($customAttributes)) {
            $this->customAttributes = [];
        } else {
            $this->customAttributes = array_merge($this->customAttributes, $customAttributes);
        }

        return $this;
    }

    /**
     * 设置验证场景数据(叠加)
     *
     * @param array|null $scene [场景 => [字段]] 如果$scene为null，则清空全部验证场景
     * @return $this
     */
    public function setScene(?array $scene = null): Validate
    {
        if (is_null($scene)) {
            $this->scene = [];
        } else {
            $this->scene = array_merge($this->scene, $scene);
        }

        return $this;
    }
}
