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
}
