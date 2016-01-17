<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Tests\ExtractionStrategy;

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

            public function b($b)
            {

            }
        };

        $s = new NamedMethodStrategy;

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
        };
        $s = new NamedMethodStrategy;

        $this->assertSame(42, $s->extract($o, 'a'));
    }
}
