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

use W7\Tests\Material\ArticleValidate;
use W7\Tests\Material\BaseTestValidate;
use W7\Tests\Material\Count;
use W7\Validate\Exception\ValidateException;
use W7\Validate\Exception\ValidateRuntimeException;
use W7\Validate\Support\ValidateScene;
use W7\Validate\Validate;
use function PHPUnit\Framework\assertEquals;

class TestSomeTimes extends Validate
{
    protected $message = [
        'a.required' => 'a不能为空',
        'b.required' => 'b不能为空',
    ];
    protected function sceneTest(ValidateScene $scene)
    {
        $scene->appendCheckField('a')
            ->appendCheckField('aIsRequired')
            ->append('aIsRequired', 'required')
            ->sometimes('a', 'required', function ($data) {
                return 1 == $data['aIsRequired'];
            });
    }

    protected function sceneTestSomeTimesMultiField(ValidateScene $scene)
    {
        $scene->only(['a', 'b'])
            ->sometimes(['a', 'b'], 'required', function () {
                return true;
            });
    }
}
class TestValidateScene extends BaseTestValidate
{
    protected $userInput;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->userInput = [
            'content' => '内容',
            'title'   => '这是一个标题'
        ];
    }

    /**
     * @test 测试没有指定验证场景的情况
     * @throws ValidateException
     */
    public function testNotScene()
    {
        $v = new ArticleValidate();
        $this->expectException(ValidateException::class);
        $v->check($this->userInput);
    }

    /**
     * @test 测试制定验证场景
     * @throws ValidateException
     */
    public function testScene()
    {
        $v    = new ArticleValidate();
        $data = $v->scene('add')->check($this->userInput);
        $this->assertEquals('内容', $data['content']);
    }

    /**
     * @test 测试自定义验证场景 edit
     * @throws ValidateException
     */
    public function testCustomScene()
    {
        $validate = new ArticleValidate();
        $this->expectException(ValidateException::class);
        $validate->scene('edit')->check($this->userInput);
    }

    /**
     * @test 测试Use自定义验证场景 save
     * @throws ValidateException
     */
    public function testUseScene()
    {
        $validate = new ArticleValidate();
        $this->expectExceptionMessage('缺少参数：文章Id');
        $validate->scene('save')->check($this->userInput);
    }

    /**
     * @test 测试移除规则
     * @throws ValidateException
     */
    public function testDynamicScene()
    {
        $validate = new ArticleValidate();
        $data     = $validate->scene('dynamic')->check([
            'title'   => '标题标题标题标题',
            'content' => '1'
        ]);
        $this->assertEquals('1', $data['content']);
    }

    /**
     * @test 测试当指定的验证场景不存在时，是否验证全部的规则
     */
    public function testNotFountSceneName()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'user' => 'required',
                'pass' => 'required'
            ];
        };

        try {
            $v->scene('notFount')->check([]);
        } catch (ValidateException $e) {
            $this->assertSame('user', $e->getAttribute());
        }
        try {
            $v->scene('notFount')->check([
                'user' => 123
            ]);
        } catch (ValidateException $e) {
            $this->assertSame('pass', $e->getAttribute());
        }
    }

    /**
     * @test 测试sometimes规则
     *
     * @throws ValidateException
     */
    public function testSometimesRule()
    {
        $v    = TestSomeTimes::make();
        $data = $v->scene('test')->check([
            'aIsRequired' => 0
        ]);

        $this->assertSame(0, $data['aIsRequired']);

        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('a不能为空');
        $v->scene('test')->check([
            'aIsRequired' => 1
        ]);
    }

    /**
     * @test 测试sometimes为多个字段附加规则
     */
    public function testSometimesMultiField()
    {
        $v = TestSomeTimes::make();
        try {
            $v->scene('testSomeTimesMultiField')->check([]);
        } catch (ValidateException $e) {
            $this->assertSame('a', $e->getAttribute());
        }

        try {
            $v->scene('testSomeTimesMultiField')->check([
                'a' => 123
            ]);
        } catch (ValidateException $e) {
            $this->assertSame('b', $e->getAttribute());
        }
    }

    /**
     * @test 测试在场景中增加闭包规则
     * @throws ValidateException
     */
    public function testAppendClosureRule()
    {
        $v = new class extends Validate {
            protected function sceneTest(ValidateScene $scene)
            {
                $scene->appendCheckField('a')
                    ->append('a', function ($attribute, $value, $fail) {
                        Count::incremental('testAppendClosureRule');
                        if (!is_numeric($value)) {
                            $fail('a必须为数字');
                        }
                    });
            }
        };

        $data = $v->scene('test')->check([
            'a' => 123
        ]);

        $this->assertSame(123, $data['a']);
        Count::assertEquals(1, 'testAppendClosureRule');

        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('a必须为数字');
        $v->scene('test')->check([
            'a' => 'aaa'
        ]);
    }

    /**
     * @test 测试在场景中获取验证的值
     * @throws ValidateException
     */
    public function testGetDataForScene()
    {
        $v                  = new class extends Validate {
            protected $rule = [
              'name' => 'required'
           ];

            protected function sceneGetData(ValidateScene $scene)
            {
                $scene->only(['name']);

                assertEquals('名字', $scene->getData('name'));
            }

            protected function sceneGetNonExistentData(ValidateScene $scene)
            {
                $scene->only(['name']);
                $test = $scene->test;
            }
        };

        $v->scene('getData')->check([
            'name' => '名字'
        ]);

        $this->expectException(ValidateRuntimeException::class);
        $v->scene('getNonExistentData')->check([
            'name' => '名字'
        ]);
    }

    /**
     * @test 测试在场景中动态移除验证规则
     */
    public function testRemoveRuleForScene()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'id' => 'required|max:100|min:1'
            ];

            protected function sceneRemoveRule(ValidateScene $scene)
            {
                $scene->only(['id'])
                    ->remove('id', 'required');
            }

            protected function sceneRemoveRuleForArray(ValidateScene $scene)
            {
                $scene->only(['id'])
                    ->remove('id', ['max', 'min']);
            }

            protected function sceneRemoveRuleForHasParams(ValidateScene $scene)
            {
                $scene->only(['id'])
                    ->remove('id', 'max:100');
            }
        };

        $rules = $v->scene('removeRule')->getRules();
        $this->assertEquals(['max:100', 'min:1'], array_values($rules['id']));

        $rules = $v->scene('removeRuleForArray')->getRules();
        $this->assertEquals(['required'], array_values($rules['id']));

        $rules = $v->scene('removeRuleForHasParams')->getRules();
        $this->assertEquals(['required', 'min:1'], array_values($rules['id']));
    }

    /**
     * @test 测试在自定义场景中动态移除字段
     */
    public function testRemoveCheckField()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'user' => 'required',
                'pass' => 'required'
            ];

            protected function sceneTest(ValidateScene $scene)
            {
                $scene->only(['user', 'pass'])
                    ->removeCheckField('pass');
            }
        };
        $rules = $v->getRules();
        $this->assertEquals(['user', 'pass'], array_keys($rules));

        $rules = $v->scene('test')->getRules();
        $this->assertEquals(['user'], array_keys($rules));
    }

    /**
     * @test 测试当场景指定的字段没有定义规则
     * @throws ValidateException
     */
    public function testSpecifyFieldUndefinedRule()
    {
        $v                   = new class extends Validate {
            protected $scene = [
                'login' => ['user', 'pass']
            ];
        };

        $data = $v->scene('login')->check([
            'user' => 1,
            'pass' => 2
        ]);

        $this->assertEquals(['user', 'pass'], array_keys($data));
    }

    /**
     * 测试当自定义场景中指定的字段没有定义规则
     * @throws ValidateException
     */
    public function testSpecifyFieldUndefinedRuleForCustomScene()
    {
        $v = new class extends Validate {
            protected function sceneLogin(ValidateScene $scene)
            {
                $scene->only(['user', 'pass']);
            }
        };

        $data = $v->scene('login')->check([
            'user' => 1,
            'pass' => 2
        ]);
        
        $this->assertEquals(['user', 'pass'], array_keys($data));
    }
}
