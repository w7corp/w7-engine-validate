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

use PHPUnit\Framework\Assert;
use W7\Tests\Material\BaseTestValidate;
use W7\Tests\Material\Count;
use W7\Tests\Material\TestValidate;
use W7\Validate\Exception\ValidateException;
use W7\Validate\Support\Event\ValidateEventAbstract;
use W7\Validate\Support\ValidateScene;
use W7\Validate\Validate;

class TestGlobalEvent extends ValidateEventAbstract
{
    public function afterValidate(): bool
    {
        Assert::assertEquals(1, Count::value('sceneEventAfter-A'));
        Assert::assertEquals(1, Count::value('sceneEventAfter-B'));
        Assert::assertEquals(1, Count::value('sceneEventAfter-C'));
        Assert::assertEquals(1, Count::value('testAfter'));
        Assert::assertEquals(1, Count::value('customSceneEventAfter'));

        Count::incremental('globalEventAfter');
        return true;
    }

    public function beforeValidate(): bool
    {
        Assert::assertEquals(0, Count::value('sceneEventBefore-A'));
        Assert::assertEquals(0, Count::value('sceneEventBefore-B'));
        Assert::assertEquals(0, Count::value('sceneEventBefore-C'));
        Assert::assertEquals(0, Count::value('testBefore'));
        Assert::assertEquals(0, Count::value('customSceneEventBefore'));

        Count::incremental('globalEventBefore');
        return true;
    }
}

class TestSceneEventA extends ValidateEventAbstract
{
    public function afterValidate(): bool
    {
        Count::incremental('sceneEventAfter-A');
        return true;
    }

    public function beforeValidate(): bool
    {
        Count::incremental('sceneEventBefore-A');
        return true;
    }
}

class TestSceneEventB extends ValidateEventAbstract
{
    public function afterValidate(): bool
    {
        Count::incremental('sceneEventAfter-B');
        return true;
    }

    public function beforeValidate(): bool
    {
        Count::incremental('sceneEventBefore-B');
        return true;
    }
}

class TestSceneEventC extends ValidateEventAbstract
{
    public function afterValidate(): bool
    {
        Count::incremental('sceneEventAfter-C');
        return true;
    }

    public function beforeValidate(): bool
    {
        Count::incremental('sceneEventBefore-C');
        return true;
    }
}

class TestCustomSceneEvent extends ValidateEventAbstract
{
    public function afterValidate(): bool
    {
        Count::incremental('customSceneEventAfter');
        return true;
    }

    public function beforeValidate(): bool
    {
        Count::incremental('customSceneEventBefore');
        return true;
    }
}
class TestHandlerEvent extends BaseTestValidate
{
    public function testErrorEvent()
    {
        $v = new TestValidate();
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('不是中文');
        $v->scene('errorEvent')->check([
            'name' => 123
        ]);
    }

    public function testEventIsCheckName()
    {
        $v = new TestValidate();
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('用户名不是admin');
        $v->scene('checkName')->check([
            'name' => 123
        ]);
    }

    public function testBeforeThrowError()
    {
        $v = new TestValidate();
        $this->expectException(ValidateException::class);
        $this->expectExceptionMessage('error');
        $v->scene('beforeThrowError')->check([]);
    }

    /**
     * @test 测试事件在场景中是否正确运行，以及全局事件和场景事件的执行顺序是否正确
     *
     * 全局事件before->场景事件->全局事件after
     * @throws ValidateException
     */
    public function testEventExecution()
    {
        $v                   = new class extends Validate {
            protected $event = [
                TestGlobalEvent::class
            ];

            protected $scene = [
                'testA' => ['event' => TestSceneEventA::class, 'next' => 'testB'],
                'testB' => ['event' => [TestSceneEventB::class, TestSceneEventC::class], 'next' => 'testC'],
                'testC' => ['before' => 'testBefore', 'after' => 'testAfter', 'next' => 'testD']
            ];

            protected function sceneTestD(ValidateScene $scene)
            {
                $scene->after('customSceneEvent')
                    ->before('customSceneEvent')
                    ->event(TestCustomSceneEvent::class);
            }

            protected function beforeCustomSceneEvent()
            {
                Count::incremental('beforeCustomSceneEvent');
                return true;
            }

            protected function afterCustomSceneEvent()
            {
                Count::incremental('afterCustomSceneEvent');
                return true;
            }

            protected function beforeTestBefore()
            {
                Count::incremental('testBefore');
                return true;
            }

            protected function afterTestAfter()
            {
                Count::incremental('testAfter');
                return true;
            }
        };

        $v->scene('testA')->check([]);

        $this->assertEquals(1, Count::value('testBefore'));
        $this->assertEquals(1, Count::value('testAfter'));

        $this->assertEquals(1, Count::value('sceneEventAfter-A'));
        $this->assertEquals(1, Count::value('sceneEventBefore-A'));

        $this->assertEquals(1, Count::value('sceneEventAfter-B'));
        $this->assertEquals(1, Count::value('sceneEventBefore-B'));

        $this->assertEquals(1, Count::value('sceneEventAfter-C'));
        $this->assertEquals(1, Count::value('sceneEventBefore-C'));

        $this->assertEquals(1, Count::value('globalEventAfter'));
        $this->assertEquals(1, Count::value('globalEventBefore'));

        $this->assertEquals(1, Count::value('customSceneEventAfter'));
        $this->assertEquals(1, Count::value('customSceneEventBefore'));
    }
}
