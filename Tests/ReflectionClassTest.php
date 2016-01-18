<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Tests;

use Innmind\Reflection\ReflectionClass;
use Innmind\Reflection\InjectionStrategy\InjectionStrategyInterface;
use Innmind\Reflection\InjectionStrategy\SetterStrategy;
use Innmind\Reflection\InjectionStrategy\NamedMethodStrategy;
use Innmind\Reflection\InjectionStrategy\ReflectionStrategy;
use Innmind\Reflection\InstanciatorInterface;
use Innmind\Reflection\Instanciator\ReflectionInstanciator;
use Innmind\Immutable\TypedCollection;
use Innmind\Immutable\CollectionInterface;

class ReflectionClassTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildWithoutProperties()
    {
        $r = new ReflectionClass(NoConstruct::class);

        $o = $r->buildObject();

        $this->assertInstanceOf(NoConstruct::class, $o);
        $this->assertSame(null, $o->a());
    }

    public function testBuild()
    {
        $o = (new ReflectionClass(NoConstruct::class))
            ->withProperty('a', 42)
            ->buildObject();

        $this->assertInstanceOf(NoConstruct::class, $o);
        $this->assertSame(42, $o->a());

        $o = (new ReflectionClass(WithConstruct::class))
            ->withProperties([
                'a' => 24,
                'b' => 66,
            ])
            ->buildObject();

        $this->assertInstanceOf(WithConstruct::class, $o);
        $this->assertSame(24, $o->a());
        $this->assertSame(66, $o->b());
    }

    public function testGetInjectionStrategies()
    {
        $refl = new ReflectionClass('stdClass');

        $s = $refl->getInjectionStrategies();
        $this->assertSame(InjectionStrategyInterface::class, $s->getType());
        $this->assertInstanceOf(SetterStrategy::class, $s[0]);
        $this->assertInstanceOf(NamedMethodStrategy::class, $s[1]);
        $this->assertInstanceOf(ReflectionStrategy::class, $s[2]);

        $refl = new ReflectionClass(
            'stdClass',
            null,
            new TypedCollection(
                InjectionStrategyInterface::class,
                [$s = new ReflectionStrategy]
            )
        );
        $this->assertSame($s, $refl->getInjectionStrategies()[0]);
    }

    public function testGetProperties()
    {
        $r = (new ReflectionClass('foo'))
            ->withProperty('a', 'b');

        $props = $r->getProperties();

        $this->assertInstanceOf(CollectionInterface::class, $props);
        $this->assertSame(['a' => 'b'], $props->toPrimitive());
    }

    public function testGetInstanciator()
    {
        $r = new ReflectionClass('foo');
        $i = $r->getInstanciator();
        $this->assertInstanceOf(ReflectionInstanciator::class, $i);

        $i = new class implements InstanciatorInterface {
            public function build(string $class, CollectionInterface $properties)
            {
            }

            public function getParameters(string $class): CollectionInterface
            {
            }
        };

        $r = new ReflectionClass('foo', null, null, $i);
        $i2 = $r->getInstanciator();
        $this->assertSame($i, $i2);
    }
}

class NoConstruct
{
    private $a;

    public function a()
    {
        return $this->a;
    }
}

class WithConstruct
{
    private $a;
    private $b;

    public function __construct($a)
    {
        $this->a = $a;
    }

    public function setA($a)
    {
        $this->a = 42;
    }

    public function a()
    {
        return $this->a;
    }

    public function b()
    {
        return $this->b;
    }
}
