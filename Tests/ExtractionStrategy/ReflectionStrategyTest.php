<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Tests\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategy\ReflectionStrategy;

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
}
