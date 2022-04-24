<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection;

use Innmind\Reflection\{
    ReflectionObject,
    ExtractionStrategy\ExtractionStrategies,
    ExtractionStrategy,
    InjectionStrategy\InjectionStrategies,
    InjectionStrategy,
    Exception\LogicException,
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class ReflectionObjectTest extends TestCase
{
    public function testBuildWithoutProperties()
    {
        $o = new \stdClass;
        $refl = ReflectionObject::of($o);

        $o2 = $refl->build();

        $this->assertSame($o, $o2);
    }

    public function testBuild()
    {
        $o = new class() {
            private $a;
            protected $b;
            private $c;
            private $d;

            public function setC($value)
            {
                $this->c = $value;
            }

            public function d($value)
            {
                $this->d = $value;
            }

            public function dump()
            {
                return [$this->a, $this->b, $this->c, $this->d];
            }
        };

        $this->assertSame([null, null, null, null], $o->dump());

        ReflectionObject::of($o)->build(Map::of(
            ['a', 1],
            ['b', 2],
            ['c', 3],
            ['d', 4],
        ));

        $this->assertSame([1, 2, 3, 4], $o->dump());
    }

    public function testThrowWhenPropertyNotFound()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Property "a" cannot be injected');

        ReflectionObject::of(new \stdClass, InjectionStrategies::all())->build(
            Map::of(['a', 1]),
        );
    }

    public function testThrowWhenNameMethodDoesntHaveParameter()
    {
        $o = new class() {
            public function a()
            {
                //pass
            }
        };

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Property "a" cannot be injected');

        ReflectionObject::of($o, InjectionStrategies::all())->build(
            Map::of(['a', 1]),
        );
    }

    public function testExtract()
    {
        $o = new class {
            public $a = 24;

            public function getB()
            {
                return 42;
            }

            public function c()
            {
                return 66;
            }
        };
        $refl = ReflectionObject::of($o, null, ExtractionStrategies::all());

        $values = $refl->extract('a', 'b', 'c');

        $this->assertInstanceOf(Map::class, $values);
        $this->assertCount(3, $values);
        $this->assertSame(24, $values->get('a')->match(
            static fn($value) => $value,
            static fn() => null,
        ));
        $this->assertSame(42, $values->get('b')->match(
            static fn($value) => $value,
            static fn() => null,
        ));
        $this->assertSame(66, $values->get('c')->match(
            static fn($value) => $value,
            static fn() => null,
        ));
    }

    public function testThrowWhenCannotExtractProperty()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Property "a" cannot be extracted');

        ReflectionObject::of(new \stdClass, null, ExtractionStrategies::all())->extract('a');
    }
}
