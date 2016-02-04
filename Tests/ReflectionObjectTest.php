<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Tests;

use Innmind\Immutable\CollectionInterface;
use Innmind\Immutable\TypedCollection;
use Innmind\Immutable\TypedCollectionInterface;
use Innmind\Reflection\ExtractionStrategy\ExtractionStrategies;
use Innmind\Reflection\ExtractionStrategy\ExtractionStrategyInterface;
use Innmind\Reflection\ExtractionStrategy\GetterStrategy;
use Innmind\Reflection\ExtractionStrategy\NamedMethodStrategy as ENamedMethodStrategy;
use Innmind\Reflection\ExtractionStrategy\ReflectionStrategy as EReflectionStrategy;
use Innmind\Reflection\ExtractionStrategy\IsserStrategy;
use Innmind\Reflection\ExtractionStrategy\HasserStrategy;
use Innmind\Reflection\InjectionStrategy\InjectionStrategies;
use Innmind\Reflection\InjectionStrategy\InjectionStrategyInterface;
use Innmind\Reflection\InjectionStrategy\NamedMethodStrategy;
use Innmind\Reflection\InjectionStrategy\ReflectionStrategy;
use Innmind\Reflection\InjectionStrategy\SetterStrategy;
use Innmind\Reflection\ReflectionObject;

class ReflectionObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildWithoutProperties()
    {
        $o = new \stdClass;
        $refl = new ReflectionObject($o);

        $o2 = $refl->buildObject();

        $this->assertSame($o, $o2);
        $this->assertSame([], $refl->getProperties()->toPrimitive());
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
        $this->assertSame([], $refl->getProperties()->toPrimitive());
        $this->assertSame(
            ['foo' => 'bar'],
            $refl2->getProperties()->toPrimitive()
        );
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
            ->buildObject();

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
            ->buildObject();

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
            ->buildObject();
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
            ->buildObject();
    }

    public function testGetInjectionStrategies()
    {
        $refl = new ReflectionObject(new \stdClass);

        $s = $refl->getInjectionStrategies()->all();
        $this->assertSame(InjectionStrategyInterface::class, $s->getType());
        $this->assertInstanceOf(SetterStrategy::class, $s[0]);
        $this->assertInstanceOf(NamedMethodStrategy::class, $s[1]);
        $this->assertInstanceOf(ReflectionStrategy::class, $s[2]);
        $this->assertSame(3, $s->count());

        $testInjectionStrategies = $this->getMockBuilder(InjectionStrategies::class)
            ->getMock();
        $testInjectionStrategies->expects($this->any())
            ->method('all')
            ->will(
                $this->returnValue(
                    new TypedCollection(
                        InjectionStrategyInterface::class,
                        [$s = new ReflectionStrategy]
                    )
                )
            );

        $refl = new ReflectionObject(
            new \stdClass,
            null,
            $testInjectionStrategies
        );
        $this->assertSame($s, $refl->getInjectionStrategies()->all()[0]);
        $this->assertSame(1, $refl->getInjectionStrategies()->all()->count());
    }

    public function testGetExtractionStrategies()
    {
        $refl = new ReflectionObject(new \stdClass);

        $s = $refl->getExtractionStrategies()->all();
        $this->assertInstanceOf(TypedCollectionInterface::class, $s);
        $this->assertSame(ExtractionStrategyInterface::class, $s->getType());
        $this->assertInstanceOf(GetterStrategy::class, $s[0]);
        $this->assertInstanceOf(ENamedMethodStrategy::class, $s[1]);
        $this->assertInstanceOf(IsserStrategy::class, $s[2]);
        $this->assertInstanceOf(HasserStrategy::class, $s[3]);
        $this->assertInstanceOf(EReflectionStrategy::class, $s[4]);
        $this->assertSame(5, $s->count());

        $testExtractionStrategies = $this->getMockBuilder(ExtractionStrategies::class)
            ->getMock();
        $testExtractionStrategies->expects($this->any())
            ->method('all')
            ->will(
                $this->returnValue(
                    new TypedCollection(
                        ExtractionStrategyInterface::class,
                        [$g = new GetterStrategy]
                    )
                )
            );

        $refl = new ReflectionObject(
            new \stdClass,
            null,
            null,
            $testExtractionStrategies
        );

        $s = $refl->getExtractionStrategies()->all();
        $this->assertInstanceOf(TypedCollectionInterface::class, $s);
        $this->assertSame(ExtractionStrategyInterface::class, $s->getType());
        $this->assertInstanceOf(GetterStrategy::class, $s[0]);
        $this->assertSame(1, $s->count());
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

        $this->assertInstanceOf(CollectionInterface::class, $values);
        $this->assertSame(3, $values->count());
        $this->assertSame(
            ['a' => 24, 'b' => 42, 'c' => 66],
            $values->toPrimitive()
        );
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
