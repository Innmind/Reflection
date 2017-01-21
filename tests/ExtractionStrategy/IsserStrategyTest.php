<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategy\IsserStrategy;
use Fixtures\Innmind\Reflection\Foo;

class IsserStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $o = new class {
            public $c;

            public function isA()
            {

            }

            public function isB($b)
            {

            }

            public function isSomeLongProperty()
            {

            }
        };

        $s = new IsserStrategy;

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
        $s = new IsserStrategy;

        $s->extract($o, 'a');
    }

    public function testExtract()
    {
        $o = new class {
            public function isA()
            {
                return true;
            }

            public function isSomeLongProperty()
            {
                return true;
            }
        };
        $s = new IsserStrategy;

        $this->assertTrue($s->extract($o, 'a'));
        $this->assertTrue($s->extract($o, 'some_long_property'));
    }

    public function testExtractWithInheritedMethod()
    {
        $strategy = new IsserStrategy;
        $object = new class extends Foo {};

        $this->assertFalse($strategy->extract($object, 'someProperty'));
    }
}
