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
use W7\Validate\Exception\ValidateException;
use W7\Validate\Support\ValidateScene;
use W7\Validate\Validate;

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
        $scene->appendCheckField('b')
            ->appendCheckField('a')
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
            $this->assertCount(2, $e->getData());
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
            $this->assertCount(2, $e->getData());
        }
    }
}
