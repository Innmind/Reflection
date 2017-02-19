<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection;

use Innmind\Reflection\ExtractionStrategy\ExtractionStrategies;
use Innmind\Reflection\ExtractionStrategyInterface;
use Innmind\Reflection\ExtractionStrategy\GetterStrategy;
use Innmind\Reflection\ExtractionStrategy\NamedMethodStrategy as ENamedMethodStrategy;
use Innmind\Reflection\ExtractionStrategy\ReflectionStrategy as EReflectionStrategy;
use Innmind\Reflection\ExtractionStrategy\IsserStrategy;
use Innmind\Reflection\ExtractionStrategy\HasserStrategy;
use Innmind\Reflection\InjectionStrategy\InjectionStrategies;
use Innmind\Reflection\InjectionStrategyInterface;
use Innmind\Reflection\InjectionStrategy\NamedMethodStrategy;
use Innmind\Reflection\InjectionStrategy\ReflectionStrategy;
use Innmind\Reflection\InjectionStrategy\SetterStrategy;
use Innmind\Reflection\ReflectionObject;
use Innmind\Immutable\{
    MapInterface,
    SetInterface,
    Set
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
        $this->assertCount(0, $refl->properties());
    }

    /**
     * @expectedException Innmind\Reflection\Exception\InvalidArgumentException
     */
    public function testThrowWhenNotUsingAnObject()
    {
        new ReflectionObject([]);
    }

    public function testAddPropertyToInject()
    {
        $o = new \stdClass;
        $refl = new ReflectionObject($o);
        $refl2 = $refl->withProperty('foo', 'bar');

        $this->assertInstanceOf(ReflectionObject::class, $refl2);
        $this->assertNotSame($refl, $refl2);
        $this->assertCount(0, $refl->properties());
        $this->assertCount(1, $refl2->properties());
        $this->assertSame('bar', $refl2->properties()->get('foo'));
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

        (new ReflectionObject($o))
            ->withProperty('a', 1)
            ->withProperty('b', 2)
            ->withProperty('c', 3)
            ->withProperty('d', 4)
            ->build();

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

        (new ReflectionObject($o))
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

    /**
     * @expectedException Innmind\Reflection\Exception\LogicException
     * @expectedExceptionMessage Property "a" cannot be injected
     */
    public function testThrowWhenPropertyNotFound()
    {
        (new ReflectionObject(new \stdClass))
            ->withProperty('a', 1)
            ->build();
    }

    /**
     * @expectedException Innmind\Reflection\Exception\LogicException
     * @expectedExceptionMessage Property "a" cannot be injected
     */
    public function testThrowWhenNameMethodDoesntHaveParameter()
    {
        $o = new class()
        {
            public function a()
            {
                //pass
            }
        };
        (new ReflectionObject($o))
            ->withProperty('a', 1)
            ->build();
    }

    public function testGetInjectionStrategy()
    {
        $refl = new ReflectionObject(new \stdClass);

        $this->assertSame(
            $refl->injectionStrategy(),
            InjectionStrategies::default()
        );

        $strategy = $this->createMock(InjectionStrategyInterface::class);
        $refl = new ReflectionObject(
            new \stdClass,
            null,
            $strategy
        );

        $this->assertSame($strategy, $refl->injectionStrategy());
    }

    public function testGetExtractionStrategy()
    {
        $refl = new ReflectionObject(new \stdClass);

        $this->assertSame(
            ExtractionStrategies::default(),
            $refl->extractionStrategy()
        );

        $strategy = $this->createMock(ExtractionStrategyInterface::class);
        $refl = new ReflectionObject(
            new \stdClass,
            null,
            null,
            $strategy
        );

        $this->assertSame($strategy, $refl->extractionStrategy());
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

        $values = $refl->extract(['a', 'b', 'c']);

        $this->assertInstanceOf(MapInterface::class, $values);
        $this->assertSame('string', (string) $values->keyType());
        $this->assertSame('mixed', (string) $values->valueType());
        $this->assertCount(3, $values);
        $this->assertSame(24, $values->get('a'));
        $this->assertSame(42, $values->get('b'));
        $this->assertSame(66, $values->get('c'));
    }

    /**
     * @expectedException Innmind\Reflection\Exception\LogicException
     * @expectedExceptionMessage Property "a" cannot be extracted
     */
    public function testThrowWhenCannotExtractProperty()
    {
        (new ReflectionObject(new \stdClass))->extract(['a']);
    }
}
