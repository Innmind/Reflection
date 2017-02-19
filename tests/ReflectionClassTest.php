<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection;

use Innmind\Reflection\InjectionStrategyInterface;
use Innmind\Reflection\InjectionStrategy\InjectionStrategies;
use Innmind\Reflection\InjectionStrategy\NamedMethodStrategy;
use Innmind\Reflection\InjectionStrategy\ReflectionStrategy;
use Innmind\Reflection\InjectionStrategy\SetterStrategy;
use Innmind\Reflection\Instanciator\ReflectionInstanciator;
use Innmind\Reflection\InstanciatorInterface;
use Innmind\Reflection\ReflectionClass;
use Innmind\Immutable\{
    MapInterface,
    SetInterface,
    Set
};
use PHPUnit\Framework\TestCase;

class ReflectionClassTest extends TestCase
{
    public function testBuildWithoutProperties()
    {
        $r = new ReflectionClass(NoConstruct::class);

        $o = $r->build();

        $this->assertInstanceOf(NoConstruct::class, $o);
        $this->assertSame(null, $o->a());
    }

    public function testBuild()
    {
        $o = (new ReflectionClass(NoConstruct::class))
            ->withProperty('a', 42)
            ->build();

        $this->assertInstanceOf(NoConstruct::class, $o);
        $this->assertSame(42, $o->a());

        $o = (new ReflectionClass(WithConstruct::class))
            ->withProperties(
                [
                    'a' => 24,
                    'b' => 66,
                ]
            )
            ->build();

        $this->assertInstanceOf(WithConstruct::class, $o);
        $this->assertSame(24, $o->a());
        $this->assertSame(66, $o->b());
    }

    public function testGetInjectionStrategy()
    {
        $refl = new ReflectionClass('stdClass');

        $this->assertSame($refl->injectionStrategy(), InjectionStrategies::default());

        $strategy = $this->createMock(InjectionStrategyInterface::class);
        $refl = new ReflectionClass(
            'stdClass',
            null,
            $strategy
        );

        $this->assertSame($strategy, $refl->injectionStrategy());
    }

    public function testGetProperties()
    {
        $r = (new ReflectionClass('foo'))
            ->withProperty('a', 'b');

        $props = $r->properties();

        $this->assertInstanceOf(MapInterface::class, $props);
        $this->assertSame('string', (string) $props->keyType());
        $this->assertSame('mixed', (string) $props->valueType());
        $this->assertCount(1, $props);
        $this->assertSame('b', $props->get('a'));
    }

    public function testGetInstanciator()
    {
        $r = new ReflectionClass('foo');
        $i = $r->instanciator();
        $this->assertInstanceOf(ReflectionInstanciator::class, $i);

        $i = new class implements InstanciatorInterface
        {
            public function build(string $class, MapInterface $properties)
            {
            }

            public function parameters(string $class): SetInterface
            {
            }
        };

        $r = new ReflectionClass('foo', null, null, $i);
        $i2 = $r->instanciator();
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
