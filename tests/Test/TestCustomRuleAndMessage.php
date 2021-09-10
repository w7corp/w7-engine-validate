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

use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Support\Arr;
use W7\Tests\Material\BaseTestValidate;
use W7\Tests\Material\Rules\Length;
use W7\Validate\Exception\ValidateException;
use W7\Validate\Support\Rule\BaseRule;
use W7\Validate\Support\ValidateScene;
use W7\Validate\Validate;

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
        $this->expectExceptionMessage('输入的字符不合格');
        
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
        $this->expectExceptionMessage('不支持该域的邮箱');
        
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
        $this->expectExceptionMessage('给定的值为空');
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
        $this->expectExceptionMessage('给定的值为空');
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
        $this->expectExceptionMessage('绑定手机号不是有效的手机号码');
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
        $this->expectExceptionMessage('绑定手机号是错误的手机号码');
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
}
