<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Tests\InjectionStrategy;

use Innmind\Reflection\InjectionStrategy\SetterStrategy;

class SetterStrategyTest extends \PHPUnit_Framework_TestCase
{
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

            public function getB()
            {
                return $this->b;
            }

            public function setSomeLongProperty($foo)
            {

            }
        };

        $this->assertTrue($s->supports($o, 'b', null));
        $this->assertTrue($s->supports($o, 'some_long_property', null));
        $this->assertFalse($s->supports($o, 'a', null));
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

        $this->assertSame(null, $s->inject($o, 'b', 'bar'));
        $this->assertSame('bar', $o->getB());
        $this->assertSame(null, $s->inject($o, 'some_long_property', 42));
        $this->assertSame(42, $o->getFoo());
    }

    /**
     * @expectedException Innmind\Reflection\Exception\LogicException
     */
    public function testThrowWhenInjectingUnsupportedProperty()
    {
        $s = new SetterStrategy;
        $o = new class {
            public $a;
        };
        $s->inject($o, 'a', 'foo');
    }
}
