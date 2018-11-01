<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection;

use Innmind\Reflection\{
    ReflectionClass,
    InjectionStrategy,
    InjectionStrategy\InjectionStrategies,
    InjectionStrategy\NamedMethodStrategy,
    InjectionStrategy\ReflectionStrategy,
    InjectionStrategy\SetterStrategy,
    Instanciator\ReflectionInstanciator,
    Instanciator
};
use Fixtures\Innmind\Reflection\{
    NoConstructor,
    WithConstructor
};
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
        $r = new ReflectionClass(NoConstructor::class);

        $o = $r->build();

        $this->assertInstanceOf(NoConstructor::class, $o);
        $this->assertSame(null, $o->a());
    }

    public function testBuild()
    {
        $o = (new ReflectionClass(NoConstructor::class))
            ->withProperty('a', 42)
            ->build();

        $this->assertInstanceOf(NoConstructor::class, $o);
        $this->assertSame(42, $o->a());

        $o = (new ReflectionClass(WithConstructor::class))
            ->withProperties(
                [
                    'a' => 24,
                    'b' => 66,
                ]
            )
            ->build();

        $this->assertInstanceOf(WithConstructor::class, $o);
        $this->assertSame(24, $o->a());
        $this->assertSame(66, $o->b());
    }

    public function testGetInjectionStrategy()
    {
        $refl = new ReflectionClass('stdClass');

        $this->assertSame($refl->injectionStrategy(), InjectionStrategies::default());

        $strategy = $this->createMock(InjectionStrategy::class);
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

        $i = new class implements Instanciator
        {
            public function build(string $class, MapInterface $properties): object
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
