<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategy\HasserStrategy;

class HasserStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $o = new class {
            public $c;

            public function hasA()
            {

            }

            public function hasB($b)
            {

            }

            public function hasSomeLongProperty()
            {

            }
        };

        $s = new HasserStrategy;

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
        $s = new HasserStrategy;

        $s->extract($o, 'a');
    }

    public function testExtract()
    {
        $o = new class {
            public function hasA()
            {
                return true;
            }

            public function hasSomeLongProperty()
            {
                return true;
            }
        };
        $s = new HasserStrategy;

        $this->assertTrue($s->extract($o, 'a'));
        $this->assertTrue($s->extract($o, 'some_long_property'));
    }
}
