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
use W7\Validate\Support\Common;
use W7\Validate\Support\Rule\BaseRule;
use W7\Validate\Support\RuleManagerScene;
use W7\Validate\Support\Storage\ValidateConfig;

class RuleManager
{
    /**
     * All original validation rules
     * @var array
     */
    protected $rule = [];

    /**
     * Define a scenario for the validation rule
     * @var array
     */
    protected $scene = [];

    /**
     * The array of custom attribute names.
     *
     * @var array
     */
    protected $customAttributes = [];

    /**
     * The array of custom error messages.
     *
     * @var array
     */
    protected $message = [];

    /**
     * Current validate scene
     * @var string|null
     */
    private $currentScene = null;

    /**
     * Extension method name
     * @var array
     */
    private static $extendName = [];

    /**
     * Implicit extension method name
     * @var array
     */
    private static $implicitRules = [];
    
    /**
     * Set current validate scene
     *
     * @param string|null $name
     * @return $this
     */
    public function scene(?string $name): RuleManager
    {
        $this->currentScene = $name;
        return $this;
    }

    /**
     * Get the name of the current validate scene
     *
     * @return string|null
     */
    public function getCurrentSceneName(): ?string
    {
        return $this->currentScene;
    }

    /**
     * Get initial rules provided
     *
     * @param string|null $sceneName The scene name, or the current scene name if not provided.
     * @return array
     */
    public function getRules(?string $sceneName = ''): array
    {
        if ('' === $sceneName) {
            $sceneName = $this->currentScene;
        }

        if (empty($sceneName)) {
            return $this->rule;
        }

        if (method_exists($this, 'scene' . ucfirst($sceneName))) {
            $scene = new RuleManagerScene($this->rule);
            call_user_func([$this, 'scene' . ucfirst($sceneName)], $scene);
            return $scene->getRules();
        }

        if (isset($this->scene[$sceneName])) {
            return array_intersect_key($this->rule, array_flip((array) $this->scene[$sceneName]));
        }

        return $this->rule;
    }

    /**
     * Converting raw rules into rules for checking data
     *
     * @param array|null $rules If this parameter is not provided, it will be retrieved by default from the `getRules` method
     * @return array|array[]
     */
    public function getCheckRules(?array $rules = null): array
    {
        if (is_null($rules)) {
            $rules = $this->getRules();
        }
        $rulesFields = array_keys($rules);
        $rule        = array_map(function ($rules, $field) {
            if (!is_array($rules)) {
                $rules = explode('|', $rules);
            }

            return array_map(function ($ruleName) use ($field) {
                if (is_string($ruleName)) {
                    $ruleClass = $this->getRuleClass($ruleName);
                    if (false !== $ruleClass) {
                        if (!empty($message = $this->getMessages($field, $ruleName))) {
                            $ruleClass->setMessage($message);
                        }
                        return $ruleClass;
                    }

                    return $this->getExtendsRule($ruleName, $field);
                }

                return $ruleName;
            }, $rules);
        }, $rules, $rulesFields);

        return array_combine($rulesFields, $rule);
    }

    /**
     * Get the instance class of a custom rule
     *
     * @param string $ruleName Custom Rule Name
     * @return false|BaseRule
     */
    private function getRuleClass(string $ruleName)
    {
        list($ruleName, $param) = Common::getKeyAndParam($ruleName, true);

        foreach (ValidateConfig::instance()->getRulePath() as $rulesPath) {
            $ruleNameSpace = $rulesPath . ucfirst($ruleName);
            if (class_exists($ruleNameSpace) && is_subclass_of($ruleNameSpace, BaseRule::class)) {
                return new $ruleNameSpace(...$param);
            }
        }

        return false;
    }

    /**
     * Register a custom validator extension.
     *
     * @param string               $rule      Rule Name
     * @param Closure|string|array $extension Closure rules, providing four parameters:$attribute, $value, $parameters, $validator
     * @param string|null          $message   Error Message
     */
    public static function extend(string $rule, $extension, ?string $message = null)
    {
        self::validatorExtend('', $rule, $extension, $message);
    }

    /**
     * Register a custom implicit validator extension.
     *
     * @param string               $rule      Rule Name
     * @param Closure|string|array $extension Closure rules, providing four parameters:$attribute, $value, $parameters, $validator
     * @param string|null          $message   Error Message
     */
    public static function extendImplicit(string $rule, $extension, ?string $message = null)
    {
        self::validatorExtend('Implicit', $rule, $extension, $message);
    }

    /**
     * Register a custom dependent validator extension.
     *
     * @param string               $rule      Rule Name
     * @param Closure|string|array $extension Closure rules, providing four parameters:$attribute, $value, $parameters, $validator
     * @param string|null          $message   Error Message
     */
    public static function extendDependent(string $rule, $extension, ?string $message = null)
    {
        self::validatorExtend('Dependent', $rule, $extension, $message);
    }

    /**
     * Register a custom validator message replacer.
     *
     * @param string         $rule     Rule Name
     * @param string|Closure $replacer Closure rules, providing four parameters:$message,$attribute,$rule,$parameters
     */
    public static function replacer(string $rule, $replacer)
    {
        if (array_key_exists($rule, self::$extendName)) {
            $ruleName = md5(get_called_class() . $rule);
            if (in_array($ruleName, self::$extendName[$rule])) {
                $rule = $ruleName;
            }
        }
        ValidateConfig::instance()->getFactory()->replacer($rule, $replacer);
    }

    /**
     * Register for custom validator extensions
     *
     * @param string               $type      Type
     * @param string               $rule      Rule Name
     * @param Closure|string|array $extension Closure rules, providing four parameters:$attribute, $value, $parameters, $validator
     * @param string|null          $message   Error Messages
     */
    private static function validatorExtend(string $type, string $rule, $extension, ?string $message = null)
    {
        // Multiple rule managers using the same rule will result in the later methods not taking effect.
        // So here a unique rule name is generated based on the namespace.
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

        ValidateConfig::instance()->getFactory()->$method($ruleName, $extension, $message);
    }

    /**
     * Get extension rules
     *
     * The method names are processed due to the need to distinguish the same custom method names for multiple validators.
     * This method is used in order to make the rules correspond to the processed method names.
     * @param string      $ruleName
     * @param string|null $field
     * @return string
     */
    private function getExtendsRule(string $ruleName, string $field = null): string
    {
        list($rule, $param) = Common::getKeyAndParam($ruleName, false);

        # Retrieve the real custom rule method name, and modify the corresponding error message
        if (array_key_exists($rule, self::$extendName)) {
            $ruleName = md5(get_called_class() . $rule);
            # Determine if an error message is defined for a custom rule method
            if (null !== $field && isset($this->message[$field . '.' . $rule])) {
                $this->message[$ruleName] = $this->message[$field . '.' . $rule];
            }

            $rule = $ruleName;
        } else {
            # If it does not exist in the current custom rule, determine if it is a class method
            # If it is a class method, register the rule to the rule manager first,
            # and then process the corresponding error message
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
     * Add the `bail` rule
     *
     * @param array $rules Original Rules
     * @return array
     */
    protected function addBailRule(array $rules): array
    {
        foreach ($rules as &$rule) {
            if (!in_array('bail', $rule)) {
                array_unshift($rule, 'bail');
            }
        }

        return $rules;
    }

    /**
     * Add the `filled` rule
     *
     * @param array $rules Original Rules
     * @return array
     */
    protected function addFilledRule(array $rules): array
    {
        $conflictRules = [
            'filled', 'nullable', 'accepted', 'present', 'required', 'required_if', 'required_unless', 'required_with',
            'required_with_all', 'required_without', 'required_without_all',
        ];

        foreach ($rules as &$rule) {
            $rulesName = array_map(function ($value) {
                if (is_object($value)) {
                    // By default, when an attribute being validated is not present or contains an empty string,
                    // normal validation rules, including custom extensions, are not run.
                    // If the ImplicitRule interface is implemented,
                    // it means that the rule object needs to be run even if the property is empty.
                    // So there is no need for the `filled` rule either,
                    // so let there be a `filled` in the array object to skip this process.
                    if ($value instanceof ImplicitRule) {
                        return 'filled';
                    } else {
                        return '';
                    }
                }

                $ruleName = Common::getKeyAndParam($value)[0];

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
     * Set validator scene data (overlay)
     *
     * @param array|null $scene [Scene => [Field]] If $Scene is null, clear all validation scenes
     * @return static
     */
    public function setScene(?array $scene = null): RuleManager
    {
        if (is_null($scene)) {
            $this->scene = [];
        } else {
            $this->scene = array_merge($this->scene, $scene);
        }

        return $this;
    }

    /**
     * Set validator rules (overlay)
     *
     * @param array|null $rules [field => rules] If $rules is null, clear all validation rules
     * @return $this
     */
    public function setRules(?array $rules = null): RuleManager
    {
        if (is_null($rules)) {
            $this->rule = [];
        } else {
            $this->rule = array_merge($this->rule, $rules);
        }

        return $this;
    }

    /**
     * Set the Message(overlay)
     *
     * @param array|null $message [Field. Rule => validation message] If $message is null,clear all validation messages
     * @return $this
     */
    public function setMessages(?array $message = null): RuleManager
    {
        if (is_null($message)) {
            $this->message = [];
        } else {
            $this->message = array_merge($this->message, $message);
        }

        return $this;
    }

    /**
     * Get the defined error message
     *
     *
     * If you want to get the error messages after validate, use the `getMessages` method of the message processor
     *
     * <p color="yellow">If you have defined an extension rule using the {@see RuleManager},
     * you need to call the `getCheckRules` method first before calling this method.
     * Otherwise the error message may not match the extension rule name.</p>
     * @param string|null $key  Full message key,if $keys is null,then get all messages
     * @param string|null $rule If the first value is a field name, the second value is a rule, otherwise leave it blank
     * @return array|string|null
     */
    public function getMessages(?string $key = null, ?string $rule = null)
    {
        if (null === $key) {
            return $this->message;
        }

        if (null !== $rule) {
            $messageName = Common::makeMessageName($key, $rule);
        } else {
            $messageName = $key;
        }

        return $this->message[$messageName] ?? '';
    }

    /**
     * Set the custom attributes(overlay)
     *
     * @param array|null $customAttributes [fields => names] If $customAttributes is null, clear all field names
     * @return $this
     */
    public function setCustomAttributes(?array $customAttributes = null): RuleManager
    {
        if (is_null($customAttributes)) {
            $this->customAttributes = [];
        } else {
            $this->customAttributes = array_merge($this->customAttributes, $customAttributes);
        }

        return $this;
    }

    /**
     * Get array of custom attribute names.
     * @return array
     */
    public function getCustomAttributes(): array
    {
        return $this->customAttributes;
    }

    public static function get($fields = null, $initial = true): array
    {
        $validate = new static();
        if (null === $fields) {
            $rules = $validate->getRules(null);
            if (!$initial) {
                $rules = $validate->getCheckRules($rules);
            }
            $message          = $validate->getMessages();
            $customAttributes = $validate->getCustomAttributes();
        } else {
            if (!is_array($fields)) {
                $fields = [$fields];
            }

            $rules = array_intersect_key($validate->getRules(null), array_flip($fields));
            if (!$initial) {
                $rules = $validate->getCheckRules($rules);
            }

            $message = array_filter($validate->getMessages(), function ($value, $key) use ($fields) {
                foreach ($fields as $field) {
                    if (0 === strrpos($key, $field)) {
                        return true;
                    }
                }
                return false;
            }, ARRAY_FILTER_USE_BOTH);

            $customAttributes = array_intersect_key($validate->getCustomAttributes(), array_flip($fields));
        }

        return [$rules, $message, $customAttributes];
    }
}
