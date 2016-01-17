<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Tests\ExtractionStrategy;

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
        };

        $s = new GetterStrategy;

        $this->assertTrue($s->supports($o, 'a'));
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
        };
        $s = new GetterStrategy;

        $this->assertSame(42, $s->extract($o, 'a'));
    }
}
