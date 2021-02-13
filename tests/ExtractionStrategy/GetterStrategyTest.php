<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\{
    ExtractionStrategy\GetterStrategy,
    ExtractionStrategy,
    Exception\LogicException,
};
use Fixtures\Innmind\Reflection\Foo;
use PHPUnit\Framework\TestCase;

class GetterStrategyTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ExtractionStrategy::class,
            new GetterStrategy
        );
    }

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

            private function getFoo()
            {
            }

            protected function getBar()
            {
            }
        };

        $s = new GetterStrategy;

        $this->assertTrue($s->supports($o, 'a'));
        $this->assertTrue($s->supports($o, 'some_long_property'));
        $this->assertFalse($s->supports($o, 'b'));
        $this->assertFalse($s->supports($o, 'c'));
        $this->assertFalse($s->supports($o, 'foo'));
        $this->assertFalse($s->supports($o, 'bar'));
    }

    public function testThrowWhenExtractingUnsuppportedProperty()
    {
        $o = new \stdClass;
        $s = new GetterStrategy;

        $this->expectException(LogicException::class);

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

    public function testExtractWithInheritedMethod()
    {
        $strategy = new GetterStrategy;
        $object = new class extends Foo {
        };

        $this->assertSame(42, $strategy->extract($object, 'someProperty'));
    }
}
