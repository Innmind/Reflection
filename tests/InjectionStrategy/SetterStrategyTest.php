<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\{
    InjectionStrategy\SetterStrategy,
    InjectionStrategy,
    Exception\LogicException,
};
use Fixtures\Innmind\Reflection\Foo;
use PHPUnit\Framework\TestCase;

class SetterStrategyTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            InjectionStrategy::class,
            new SetterStrategy,
        );
    }

    public function testSupports()
    {
        $s = new SetterStrategy;
        $o = new class {
            private $a;
            private $b;

            public function setB($b)
            {
                $this->b = $b;
            }

            public function setSomeLongProperty($foo)
            {
            }

            private function setFoo($foo)
            {
            }

            private function setBar($bar)
            {
            }
        };

        $this->assertTrue($s->supports($o, 'b', null));
        $this->assertTrue($s->supports($o, 'some_long_property', null));
        $this->assertFalse($s->supports($o, 'a', null));
        $this->assertFalse($s->supports($o, 'foo', null));
        $this->assertFalse($s->supports($o, 'bar', null));
    }

    public function testInject()
    {
        $s = new SetterStrategy;
        $o = new class {
            private $b;
            private $foo;

            public function setB($b)
            {
                $this->b = $b;
            }

            public function getB()
            {
                return $this->b;
            }

            public function setSomeLongProperty($foo)
            {
                $this->foo = $foo;
            }

            public function getFoo()
            {
                return $this->foo;
            }
        };

        $this->assertSame($o, $s->inject($o, 'b', 'bar'));
        $this->assertSame('bar', $o->getB());
        $this->assertSame($o, $s->inject($o, 'some_long_property', 42));
        $this->assertSame(42, $o->getFoo());
    }

    public function testThrowWhenInjectingUnsupportedProperty()
    {
        $s = new SetterStrategy;
        $o = new class {
            public $a;
        };

        $this->expectException(LogicException::class);

        $s->inject($o, 'a', 'foo');
    }

    public function testInjectWithInheritedMethod()
    {
        $strategy = new SetterStrategy;
        $object = new class extends Foo {
        };

        $this->assertSame(42, $object->someProperty());
        $this->assertSame($object, $strategy->inject($object, 'someProperty', 24));
        $this->assertSame(24, $object->someProperty());
    }
}
