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

namespace W7\Tests\Test;

use Itwmw\Validation\Support\Interfaces\ImplicitRule;
use Itwmw\Validation\Support\Arr;
use W7\Tests\Material\BaseTestValidate;
use W7\Tests\Material\Rules\Length;
use W7\Validate\Exception\ValidateException;
use W7\Validate\Support\Rule\BaseRule;
use W7\Validate\Support\ValidateScene;
use W7\Validate\Validate;
use function PHPUnit\Framework\assertEquals;

class TestCustomRuleA extends Validate
{
    protected $rule = [
        'num' => 'numberIsTen'
    ];

    protected $message = [
        'num.numberIsTen' => '给定的参数不是10'
    ];

    protected function ruleNumberIsTen($att, $value): bool
    {
        return 10 === (int)$value;
    }
}

class TestCustomRuleB extends TestCustomRuleA
{
    protected $message = [
        'num.numberIsTen' => '给定的参数不是十'
    ];

    protected function ruleNumberIsTen($att, $value): bool
    {
        return '十' === (string)$value;
    }
}
class TestExtendRule extends Validate
{
    public function __construct()
    {
        self::extend('mobile', function ($attribute, $value) {
            return is_scalar($value) && 1 === preg_match('/^1[3-9]\d{9}$/', (string)$value);
        }, ':attribute不是有效的手机号码');
    }

    protected $rule = [
        'bind' => 'mobile'
    ];

    protected $customAttributes = [
        'bind' => '绑定手机号'
    ];

    protected function sceneReplacerMobileMessage(ValidateScene $scene)
    {
        $scene->only(['bind']);
        self::replacer('mobile', function ($message, $attribute, $rule, $parameters) {
            return ($this->customAttributes[$attribute] ?? $attribute) . '是错误的手机号码';
        });
    }
}

class TestImplicitRule extends Validate
{
    public function __construct()
    {
        self::extendImplicit('isNotEmpty', function ($attribute, $value) {
            return !empty($value);
        }, '给定的值为空');
    }

    protected $rule = [
        'content' => 'isNotEmpty'
    ];
}

class TestDependentRule extends Validate
{
    public function __construct()
    {
        self::extendDependent('contains', function ($attribute, $value, $parameters, $validator) {
            return str_contains($value, Arr::get($validator->getData(), $parameters[0]));
        }, '不支持该域的邮箱');
    }

    protected $rule = [
        '*.email' => 'contains:*.provider'
    ];
}

class TestImplicitRuleClass extends BaseRule implements ImplicitRule
{
    protected $message = '给定的值为空';

    public function passes($attribute, $value): bool
    {
        return !empty($value);
    }
}

class TestCustomRuleAndMessage extends BaseTestValidate
{
    public function testCustomRuleIsObject()
    {
        $v = Validate::make([
            'id' => [
                new class extends BaseRule {
                    protected $message = '输入的字符不合格';

                    public function passes($attribute, $value): bool
                    {
                        return is_numeric($value);
                    }
                }
            ]
        ]);

        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^输入的字符不合格$/');
        
        $v->check([
            'id' => 'aaa'
        ]);
    }
    /**
     * @test 测试依赖规则
     *
     * @throws ValidateException
     */
    public function testDependentRule()
    {
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^不支持该域的邮箱$/');
        
        TestDependentRule::make()->check([
            ['email' => '995645888@qq.com', 'provider' => 'qq.com'],
            ['email' => '351409246@qq.com', 'provider' => 'qq.com'],
            ['email' => 'admin@itwmw.com', 'provider' => 'qq.com']
        ]);
    }

    /**
     * @test 测试当值为空，规则也依旧执行(方法扩展)
     * @throws ValidateException
     */
    public function testImplicitRule()
    {
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^给定的值为空$/');
        TestImplicitRule::make()->check([]);
    }

    /**
     * @test 测试当值为空，规则也依旧执行(规则类)
     *
     * @throws ValidateException
     */
    public function testImplicitRuleForRuleClass()
    {
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^给定的值为空$/');
        Validate::make([
            'a' => [
                new TestImplicitRuleClass()
            ]
        ])->check([]);
    }

    /**
     * @test 测试扩展规则和对应的错误消息是否生效
     * @throws ValidateException
     */
    public function testExtendRule()
    {
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^绑定手机号不是有效的手机号码$/');
        TestExtendRule::make()->check([
            'bind' => 123
        ]);
    }

    /**
     * @test 测试修改扩展规则对应的错误消息
     * @throws ValidateException
     */
    public function testReplacerErrorMessage()
    {
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^绑定手机号是错误的手机号码$/');
        TestExtendRule::make()->scene('replacerMobileMessage')->check([
            'bind' => 123
        ]);
    }

    /**
     * @test 测试多个验证器定义相同的规则名，规则是否会冲突
     */
    public function testSameNameRule()
    {
        try {
            $data = TestCustomRuleA::make()->check([
                'num' => 0
            ]);
        } catch (ValidateException $e) {
            $this->assertSame('给定的参数不是10', $e->getMessage(), '返回的错误消息不符合预期');
        }
        $this->assertFalse(isset($data), '验证错误的通过');

        try {
            $data = TestCustomRuleB::make()->check([
                'num' => 10
            ]);
        } catch (ValidateException $e) {
            $this->assertSame('给定的参数不是十', $e->getMessage(), '返回的错误消息不符合预期');
        }

        $this->assertFalse(isset($data), '验证错误的通过');
    }

    /**
     * @ test 规则单独使用
     */
    public function testSeparateUseRules()
    {
        $this->assertTrue(Length::make(5)->check(12345));
        $this->assertFalse(Length::make(5)->check(1234));
    }

    /**
     * @test 测试传递参数到自定义规则
     * @throws ValidateException
     */
    public function testPassingParamsToCustomRules()
    {
        $v                  = new class extends Validate {
            protected $rule = [
               'a' => 'test:111'
           ];

            protected function ruleTest($attribute, $value, $parameters)
            {
                assertEquals(111, $parameters[0]);
                return false;
            }

            protected $message = [
              'a.test' => 'testErrorMessage'
           ];
        };

        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^testErrorMessage$/');
        $v->check([
            'a' => 123
        ]);
    }

    /**
     * @test 测试全局定义的规则
     * @throws ValidateException
     */
    public function testGlobalExtendRule()
    {
        Validate::extend('sex', function ($attribute, $value) {
            return in_array($value, ['男', '女']);
        }, '请输入主流性别');

        $v                  = new class extends Validate {
            protected $rule = [
                'sex' => 'required|sex'
            ];
        };

        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^请输入主流性别$/');
        $v->check([
            'sex' => 1
        ]);
    }

    /**
     * @test 测试当全局规则和类规则名称相同时的优先级处理是否符合预期
     * @depends testGlobalExtendRule
     */
    public function testExtendRulePriority()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'sex' => 'required|sex'
            ];

            protected function ruleSex($attribute, $value): bool
            {
                return in_array($value, [0, 1]);
            }
        };

        $data = $v->check([
            'sex' => 1
        ]);

        $this->assertSame(1, $data['sex']);
    }

    /**
     * @test 测试在类中使用`extendImplicitRule`方法扩展存在规则
     * @throws ValidateException
     */
    public function testExtendImplicitRuleInClass()
    {
        $v = new class extends Validate {
            public function __construct()
            {
                $this->extendImplicitRule('empty', function ($attribute, $value) {
                    return !empty($value);
                }, ':attribute参数不可为空');
            }

            protected $rule = [
                'name' => 'empty'
            ];
        };

        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^name参数不可为空$/');
        $v->check([]);
    }

    /**
     * @test 测试在类中使用`extendDependentRule`方法扩展依赖规则
     */
    public function testExtendDependentRuleInClass()
    {
        $v = new class extends Validate {
            public function __construct()
            {
                $this->extendDependentRule('contains', function ($attribute, $value, $parameters, $validator) {
                    return str_contains($value, Arr::get($validator->getData(), $parameters[0]));
                }, '不支持该域的邮箱');
            }

            protected $rule = [
                '*.email' => 'contains:*.provider'
            ];
        };

        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^不支持该域的邮箱$/');

        $v->check([
            ['email' => '995645888@qq.com', 'provider' => 'qq.com'],
            ['email' => '351409246@qq.com', 'provider' => 'qq.com'],
            ['email' => 'admin@itwmw.com', 'provider' => 'qq.com']
        ]);
    }

    /**
     * @test 测试替换全局规则的错误消息
     * @depends testGlobalExtendRule
     */
    public function testReplacerGlobalRuleMessage()
    {
        Validate::replacer('sex', function () {
            return ':attribute错误,请输入正确性别';
        });

        $this->expectException(ValidateException::class);
        $this->expectExceptionMessageMatches('/^性别错误,请输入正确性别$/');

        Validate::make([
            'sex' => 'required|sex'
        ], [], [
            'sex' => '性别'
        ])->check([
            'sex' => 666
        ]);
    }
}
