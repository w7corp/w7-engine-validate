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

namespace W7\Tests;

use W7\Tests\Material\TestArticleValidate;
use W7\Tests\Material\TestBaseValidate;
use W7\Validate\Exception\ValidateException;

class TestValidateScene extends TestBaseValidate
{
	/** @var array */
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
		$v = new TestArticleValidate();
		$this->expectException(ValidateException::class);
		$v->check($this->userInput);
	}

	/**
	 * @test 测试制定验证场景
	 * @throws ValidateException
	 */
	public function testScene()
	{
		$v    = new TestArticleValidate();
		$data = $v->scene('add')->check($this->userInput);
		$this->assertEquals('内容', $data['content']);
	}
	
	/**
	 * @test 测试自定义验证场景 edit
	 * @throws ValidateException
	 */
	public function testCustomScene()
	{
		$validate = new TestArticleValidate();
		$this->expectException(ValidateException::class);
		$validate->scene('edit')->check($this->userInput);
	}
	
	/**
	 * @test 测试Use自定义验证场景 save
	 * @throws ValidateException
	 */
	public function testUseScene()
	{
		$validate = new TestArticleValidate();
		$this->expectExceptionMessage('缺少参数：文章Id');
		$validate->scene('save')->check($this->userInput);
	}
	
	public function testDynamicScene()
	{
		$validate = new TestArticleValidate();
		$data     = $validate->scene('dynamic')->check([
			'title'   => '标题标题标题标题',
			'content' => '1'
		]);
		$this->assertEquals('1', $data['content']);
	}
}
