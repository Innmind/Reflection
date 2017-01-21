<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategy\ReflectionStrategy;
use Fixtures\Innmind\Reflection\Foo;

class ReflectionStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $o = new class {
            public $a;
            private $b;
            protected $c;
        };

        $s = new ReflectionStrategy;

        $this->assertTrue($s->supports($o, 'a'));
        $this->assertTrue($s->supports($o, 'b'));
        $this->assertTrue($s->supports($o, 'c'));
        $this->assertFalse($s->supports($o, 'd'));

        $std = new \stdClass;
        $std->foo = 'bar';

        $this->assertTrue($s->supports($std, 'foo'));
    }

    public function testSupportsInheritMethod()
    {
        $strategy = new ReflectionStrategy;
        $object = new class extends Foo {};

        $this->assertTrue($strategy->supports($object, 'someProperty'));
    }

    /**
     * @expectedException Innmind\Reflection\Exception\LogicException
     */
    public function testThrowWhenExtractingUnsuppportedProperty()
    {
        $o = new \stdClass;
        $s = new ReflectionStrategy;

        $s->extract($o, 'a');
    }

    public function testExtract()
    {
        $o = new class {
            public $a = 24;
            private $b = 42;
            protected $c = 66;
        };
        $s = new ReflectionStrategy;

        $this->assertSame(24, $s->extract($o, 'a'));
        $this->assertSame(42, $s->extract($o, 'b'));
        $this->assertSame(66, $s->extract($o, 'c'));
    }

    public function testExtractInheritedProperty()
    {
        $child = new class extends Foo {};

        $strategy = new ReflectionStrategy;

        $this->assertSame(42, $strategy->extract($child, 'someProperty'));
    }

    public function testExtractDynamicallySetProperty()
    {
        $strategy = new ReflectionStrategy;
        $std = new \stdClass;
        $std->foo = 'bar';

        $this->assertSame('bar', $strategy->extract($std, 'foo'));
    }
}
