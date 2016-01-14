<?php

namespace Innmind\Reflection\Tests\InjectionStrategy;

use Innmind\Reflection\InjectionStrategyInterface;
use Innmind\Reflection\InjectionStrategy\NamedMethodStrategy;

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
        };

        $this->assertTrue($s->supports($o, 'b', null));
        $this->assertFalse($s->supports($o, 'a', null));
    }

    public function testInject()
    {
        $s = new NamedMethodStrategy;
        $o = new class {
            private $b;

            public function b($b)
            {
                $this->b = $b;
            }

            public function getB()
            {
                return $this->b;
            }
        };

        $this->assertSame(null, $s->inject($o, 'b', 'bar'));
        $this->assertSame('bar', $o->getB());
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
}
