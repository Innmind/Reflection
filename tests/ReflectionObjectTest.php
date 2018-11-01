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
use Innmind\Immutable\{
    MapInterface,
    SetInterface,
    Set,
};
use PHPUnit\Framework\TestCase;

class ReflectionObjectTest extends TestCase
{
    public function testBuildWithoutProperties()
    {
        $o = new \stdClass;
        $refl = new ReflectionObject($o);

        $o2 = $refl->build();

        $this->assertSame($o, $o2);
    }

    public function testAddPropertyToInject()
    {
        $o = new \stdClass;
        $refl = new ReflectionObject($o);
        $refl2 = $refl->withProperty('foo', 'bar');

        $this->assertInstanceOf(ReflectionObject::class, $refl2);
        $this->assertNotSame($refl, $refl2);
    }

    public function testBuild()
    {
        $o = new class()
        {
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

        $result = ReflectionObject::of($o)
            ->withProperty('a', 1)
            ->withProperty('b', 2)
            ->withProperty('c', 3)
            ->withProperty('d', 4)
            ->build();

        $this->assertSame($o, $result);
        $this->assertSame([1, 2, 3, 4], $o->dump());
    }

    public function testBuildWithProperties()
    {
        $o = new class()
        {
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

        ReflectionObject::of($o)
            ->withProperties(
                [
                    'a' => 1,
                    'b' => 2,
                ]
            )
            ->withProperties(
                [
                    'c' => 3,
                    'd' => 4,
                ]
            )
            ->build();

        $this->assertSame([1, 2, 3, 4], $o->dump());
    }

    public function testThrowWhenPropertyNotFound()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Property "a" cannot be injected');

        ReflectionObject::of(new \stdClass)
            ->withProperty('a', 1)
            ->build();
    }

    public function testThrowWhenNameMethodDoesntHaveParameter()
    {
        $o = new class()
        {
            public function a()
            {
                //pass
            }
        };

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Property "a" cannot be injected');

        ReflectionObject::of($o)
            ->withProperty('a', 1)
            ->build();
    }

    public function testExtract()
    {
        $o = new class
        {
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
        $refl = new ReflectionObject($o);

        $values = $refl->extract('a', 'b', 'c');

        $this->assertInstanceOf(MapInterface::class, $values);
        $this->assertSame('string', (string) $values->keyType());
        $this->assertSame('mixed', (string) $values->valueType());
        $this->assertCount(3, $values);
        $this->assertSame(24, $values->get('a'));
        $this->assertSame(42, $values->get('b'));
        $this->assertSame(66, $values->get('c'));
    }

    public function testThrowWhenCannotExtractProperty()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Property "a" cannot be extracted');

        ReflectionObject::of(new \stdClass)->extract('a');
    }
}
