<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategy\NamedMethodStrategy;

class NamedMethodStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $o = new class {
            public $c;

            public function a()
            {

            }

            public function someLongProperty()
            {

            }

            public function b($b)
            {

            }
        };

        $s = new NamedMethodStrategy;

        $this->assertTrue($s->supports($o, 'a'));
        $this->assertTrue($s->supports($o, 'some_long_property'));
        $this->assertFalse($s->supports($o, 'b'));
        $this->assertFalse($s->supports($o, 'c'));
    }

    /**
     * @expectedException Innmind\Reflection\Exception\LogicException
     */
    public function testThrowWhenExtractingUnsuppportedProperty()
    {
        $o = new \stdClass;
        $s = new NamedMethodStrategy;

        $s->extract($o, 'a');
    }

    public function testExtract()
    {
        $o = new class {
            public function a()
            {
                return 42;
            }

            public function someLongProperty()
            {
                return 66;
            }
        };
        $s = new NamedMethodStrategy;

        $this->assertSame(42, $s->extract($o, 'a'));
        $this->assertSame(66, $s->extract($o, 'some_long_property'));
    }
}
