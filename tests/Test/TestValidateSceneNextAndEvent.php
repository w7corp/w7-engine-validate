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
use W7\Validate\Support\Event\ValidateEventAbstract;
use W7\Validate\Validate;

class Count
{
    public static $globalEventCount     = 0;
    public static $eventInSceneCount    = 0;
    public static $standaloneEventCount = 0;
}
class TestEvent extends ValidateEventAbstract
{
    public function afterValidate(): bool
    {
        Count::$globalEventCount++;
        return true;
    }

    public function beforeValidate(): bool
    {
        Count::$globalEventCount++;
        return true;
    }
}

class EventInScene extends ValidateEventAbstract
{
    public function afterValidate(): bool
    {
        Count::$eventInSceneCount++;
        return true;
    }

    public function beforeValidate(): bool
    {
        Count::$eventInSceneCount++;
        return true;
    }
}

class StandaloneEvent extends ValidateEventAbstract
{
    public function afterValidate(): bool
    {
        Count::$standaloneEventCount++;
        return true;
    }

    public function beforeValidate(): bool
    {
        Count::$standaloneEventCount++;
        return true;
    }
}
class TestValidateSceneNextAndEvent extends BaseTestValidate
{
    public function testAssociatedSceneEvents()
    {
        $v                 = new class extends Validate {
            public $afters = [];

            public $befores = [];

            protected $rule = [
                'a' => 'required',
                'b' => 'required',
                'c' => 'required',
            ];

            protected $event = [
                TestEvent::class
            ];

            protected $scene = [
                'testA' => ['before' => 'aEvent', 'a', 'next' => 'testB', 'after' => 'aEvent'],
                'testB' => ['before' => 'bEvent', 'b', 'next' => 'testC', 'after' => 'bEvent', 'event' => EventInScene::class],
                'testC' => ['c', 'event' => EventInScene::class],

                'standalone' => ['a', 'before' => 'standalone', 'after' => 'standalone', 'event' => StandaloneEvent::class]
            ];

            protected $filter = [
                'a' => 'intval'
            ];

            protected function afterStandalone(array $data)
            {
                Assert::assertEquals('string', gettype($data['a']));
                $this->afters['standalone'] = ($this->afters['standalone'] ?? 0) + 1;
                return true;
            }

            protected function beforeStandalone()
            {
                $this->befores['standalone'] = ($this->befores['standalone'] ?? 0) + 1;
                return true;
            }

            protected function afterAEvent($data)
            {
                Assert::assertEquals('string', gettype($data['a']));
                $this->afters['a'] = ($this->afters['a'] ?? 0) + 1;
                return true;
            }

            protected function beforeAEvent($data)
            {
                $this->befores['a'] = ($this->befores['a'] ?? 0) + 1;
                return true;
            }

            protected function afterBEvent($data)
            {
                $this->afters['b'] = ($this->afters['b'] ?? 0) + 1;
                return true;
            }

            protected function beforeBEvent($data)
            {
                Assert::assertEquals('string', gettype($data['a']));
                $this->befores['b'] = ($this->befores['b'] ?? 0) + 1;
                return true;
            }
        };

        $this->assertEquals(0, Count::$globalEventCount);
        $this->assertEquals(0, Count::$eventInSceneCount);
        $data = $v->scene('testA')->check([
            'a' => '1',
            'b' => 2,
            'c' => 3
        ]);
        $this->assertEquals(2, Count::$globalEventCount);
        $this->assertEquals(4, Count::$eventInSceneCount);

        $this->assertArrayHasKey('a', $v->afters);
        $this->assertArrayHasKey('a', $v->befores);

        $this->assertArrayHasKey('b', $v->afters);
        $this->assertArrayHasKey('b', $v->befores);

        $this->assertEquals(1, $v->afters['a']);
        $this->assertEquals(1, $v->afters['b']);

        $this->assertEquals(1, $v->befores['a']);
        $this->assertEquals(1, $v->befores['b']);

        $this->assertEquals('integer', gettype($data['a']));
        $this->assertTrue(empty(array_diff_key($data, array_flip(['a', 'b', 'c']))));

        $this->assertEquals(0, Count::$standaloneEventCount);
        $data = $v->scene('standalone')->check([
            'a' => '1'
        ]);
        $this->assertEquals(2, Count::$standaloneEventCount);
        
        $this->assertArrayHasKey('standalone', $v->afters);
        $this->assertArrayHasKey('standalone', $v->befores);

        $this->assertEquals(1, $v->afters['standalone']);
        $this->assertEquals(1, $v->befores['standalone']);

        $this->assertEquals(1, $data['a']);
    }
}
