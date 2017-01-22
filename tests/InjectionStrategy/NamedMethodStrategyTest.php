<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\InjectionStrategy\NamedMethodStrategy;
use Fixtures\Innmind\Reflection\Foo;

class NamedMethodStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $s = new NamedMethodStrategy;
        $o = new class {
            private $a;
            private $b;

            public function b($b)
            {
                $this->b = $b;
            }

            public function getB()
            {
                return $this->b;
            }

            public function someLongProperty($foo)
            {

            }
        };

        $this->assertTrue($s->supports($o, 'b', null));
        $this->assertTrue($s->supports($o, 'some_long_property', null));
        $this->assertFalse($s->supports($o, 'a', null));
    }

    public function testInject()
    {
        $s = new NamedMethodStrategy;
        $o = new class {
            private $b;
            private $foo;

            public function b($b)
            {
                $this->b = $b;
            }

            public function getB()
            {
                return $this->b;
            }

            public function someLongProperty($foo)
            {
                $this->foo = $foo;
            }

            public function foo()
            {
                return $this->foo;
            }
        };

        $this->assertSame(null, $s->inject($o, 'b', 'bar'));
        $this->assertSame('bar', $o->getB());
        $this->assertSame(null, $s->inject($o, 'some_long_property', 42));
        $this->assertSame(42, $o->foo());
    }

    /**
     * @expectedException Innmind\Reflection\Exception\LogicException
     */
    public function testThrowWhenInjectingUnsupportedProperty()
    {
        $s = new NamedMethodStrategy;
        $o = new class {
            public $a;
        };
        $s->inject($o, 'a', 'foo');
    }

    public function testInjectWithInheritedMethod()
    {
        $strategy = new NamedMethodStrategy;
        $object = new class extends Foo {};

        $this->assertSame(42, $object->someProperty());
        $this->assertNull($strategy->inject($object, 'someProperty', 24));
        $this->assertSame(24, $object->someProperty());
    }
}