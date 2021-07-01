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

namespace W7\Tests\Test\Fixer;

use W7\Tests\Material\BaseTestValidate;
use W7\Tests\Material\Count;
use W7\Validate\Exception\ValidateException;
use W7\Validate\Support\ValidateScene;
use W7\Validate\Validate;

class TestScene extends BaseTestValidate
{
    /**
     * @test 测试在场景中获取当前验证数据为空的问题
     * @see https://gitee.com/we7coreteam/w7-engine-validate/pulls/5
     * @throws ValidateException
     */
    public function testSceneCheckDataIsEmpty()
    {
        $v                    = new class extends Validate {
            public $checkData = [];

            protected $rule = [
                'name' => 'required'
            ];

            protected function sceneTest(ValidateScene $scene)
            {
                $this->checkData = $scene->getData();
                $scene->only(['name']);
            }
        };

        $v->scene('test')->check([
            'name' => 123
        ]);

        $this->assertArrayHasKey('name', $v->checkData);
        $this->assertEquals(123, $v->checkData['name']);
    }

    /**
     * @test 测试场景中添加字段
     * @see https://gitee.com/we7coreteam/w7-engine-validate/pulls/3
     * @see https://gitee.com/we7coreteam/w7-engine-validate/pulls/6
     * @throws ValidateException
     */
    public function testSceneAppendCheckField()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'a' => 'required',
                'b' => 'required',
                'c' => 'required',
            ];

            protected $message = [
                'a.required' => 'a不能为空',
                'b.required' => 'b不能为空',
                'c.required' => 'c不能为空',
            ];

            protected function sceneTest(ValidateScene $scene)
            {
                $scene->only(['a'])
                    ->appendCheckField('b')
                    ->appendCheckField('c');
            }
        };

        try {
            $v->scene('test')->check([
                'a' => 1
            ]);
        } catch (ValidateException $e) {
            $this->assertEquals('b不能为空', $e->getMessage());
        }

        try {
            $v->scene('test')->check([
                'a' => 1,
                'b' => 1
            ]);
        } catch (ValidateException $e) {
            $this->assertEquals('c不能为空', $e->getMessage());
        }

        $data = $v->scene('test')->check([
            'a' => 1,
            'b' => 1,
            'c' => 1,
        ]);

        $this->assertEmpty(array_diff_key($data, array_flip(['a', 'b', 'c'])));
    }

    /**
     * @test 测试在next场景中，如果返回空场景名导致的问题
     * @see https://gitee.com/we7coreteam/w7-engine-validate/commit/db5ba9de603ac9e167fd46d2b90826595060813b
     * @throws ValidateException
     */
    public function testNextSceneIsEmpty()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'a' => 'required'
            ];

            protected $scene = [
                'testA' => ['a', 'next' => 'test'],
            ];

            protected function testSelector(): string
            {
                Count::incremental('emptyScene');
                return '';
            }
        };
        
        $data = $v->scene('testA')->check(['a' => 1]);
        $this->assertEquals(1, Count::value('emptyScene'));
        $this->assertEquals(1, $data['a']);
    }

    /**
     * @test 测试在next中规则为原始规则的BUG
     * @see https://gitee.com/we7coreteam/w7-engine-validate/commit/f0cefc381e3dd90a8faf69514eb6a2d6016ede77
     * @throws ValidateException
     */
    public function testRulesAreNotParsedForNext()
    {
        $v                  = new class extends Validate {
            protected $rule = [
                'a' => 'required|numeric|min:1|test',
                'b' => 'required|numeric|min:1',
            ];

            protected $scene = [
                'testA'    => ['a', 'next' => 'testNext'],
                'testNext' => ['a', 'b']
            ];

            protected function sceneTestB(ValidateScene $scene)
            {
                $scene->only(['a'])->next('testNext');
            }

            protected function ruleTest()
            {
                return true;
            }
        };

        $data = $v->scene('testA')->check(['a' => 2, 'b' => 3]);
        $this->assertEquals(2, $data['a']);

        $data = $v->scene('testB')->check(['a' => 2, 'b' => 3]);
        $this->assertEquals(2, $data['a']);
    }
}
