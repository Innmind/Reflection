<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Tests\InjectionStrategy;

use Innmind\Reflection\InjectionStrategyInterface;
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
        };

        $this->assertTrue($s->supports($o, 'b', null));
        $this->assertFalse($s->supports($o, 'a', null));
    }

    public function testInject()
    {
        $s = new SetterStrategy;
        $o = new class {
            private $b;

            public function setB($b)
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
        $s = new SetterStrategy;
        $o = new class {
            public $a;
        };
        $s->inject($o, 'a', 'foo');
    }
}
