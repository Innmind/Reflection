<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\InjectionStrategy\ReflectionStrategy;

class ReflectionStrategyTest extends \PHPUnit_Framework_TestCase
{
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

        $this->assertSame(null, $s->inject($o, 'a', 'bar'));
        $this->assertSame('bar', $o->a());
        $this->assertSame(null, $s->inject($o, 'b', 'bar'));
        $this->assertSame('bar', $o->b());
        $this->assertSame(null, $s->inject($o, 'c', 'bar'));
        $this->assertSame('bar', $o->c);
    }

    /**
     * @expectedException Innmind\Reflection\Exception\LogicException
     */
    public function testThrowWhenInjectingUnsupportedProperty()
    {
        $s = new ReflectionStrategy;
        $o = new class {
            public $b;
        };
        $s->inject($o, 'a', 'foo');
    }
}
