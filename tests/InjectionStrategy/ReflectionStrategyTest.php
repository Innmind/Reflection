<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\{
    InjectionStrategy\ReflectionStrategy,
    InjectionStrategy,
    Exception\LogicException,
};
use Fixtures\Innmind\Reflection\Foo;
use PHPUnit\Framework\TestCase;

class ReflectionStrategyTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            InjectionStrategy::class,
            new ReflectionStrategy
        );
    }

    public function testSupports()
    {
        $s = new ReflectionStrategy;
        $o = new class {
            private $a;
        };

        $this->assertTrue($s->supports($o, 'a', null));
        $this->assertFalse($s->supports($o, 'b', null));
    }

    public function testInject()
    {
        $s = new ReflectionStrategy;
        $o = new class {
            private $a;
            protected $b;
            public $c;

            public function a()
            {
                return $this->a;
            }

            public function b()
            {
                return $this->b;
            }
        };

        $this->assertSame($o, $s->inject($o, 'a', 'bar'));
        $this->assertSame('bar', $o->a());
        $this->assertSame($o, $s->inject($o, 'b', 'bar'));
        $this->assertSame('bar', $o->b());
        $this->assertSame($o, $s->inject($o, 'c', 'bar'));
        $this->assertSame('bar', $o->c);
    }

    public function testInjectInheritedProperty()
    {
        $strategy = new ReflectionStrategy;
        $object = new class extends Foo {};

        $this->assertSame(42, $object->someProperty());
        $this->assertSame($object, $strategy->inject($object, 'someProperty', 24));
        $this->assertSame(24, $object->someProperty());
    }

    public function testThrowWhenInjectingUnsupportedProperty()
    {
        $s = new ReflectionStrategy;
        $o = new class {
            public $b;
        };

        $this->expectException(LogicException::class);

        $s->inject($o, 'a', 'foo');
    }
}
