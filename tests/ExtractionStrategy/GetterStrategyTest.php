<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategy\GetterStrategy;

class GetterStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $o = new class {
            public $c;

            public function getA()
            {

            }

            public function getB($b)
            {

            }

            public function getSomeLongProperty()
            {

            }
        };

        $s = new GetterStrategy;

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
        $s = new GetterStrategy;

        $s->extract($o, 'a');
    }

    public function testExtract()
    {
        $o = new class {
            public function getA()
            {
                return 42;
            }

            public function getSomeLongProperty()
            {
                return 66;
            }
        };
        $s = new GetterStrategy;

        $this->assertSame(42, $s->extract($o, 'a'));
        $this->assertSame(66, $s->extract($o, 'some_long_property'));
    }
}
