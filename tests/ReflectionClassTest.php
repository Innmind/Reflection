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
    Instanciator,
};
use Fixtures\Innmind\Reflection\{
    NoConstructor,
    WithConstructor,
};
use Innmind\Immutable\{
    Set,
    Map,
};
use PHPUnit\Framework\TestCase;

class ReflectionClassTest extends TestCase
{
    public function testBuildWithoutProperties()
    {
        $r = ReflectionClass::of(NoConstructor::class);

        $o = $r->build();

        $this->assertInstanceOf(NoConstructor::class, $o);
        $this->assertNull($o->a());
    }

    public function testBuild()
    {
        $o = ReflectionClass::of(NoConstructor::class)->build(
            Map::of(['a', 42]),
        );

        $this->assertInstanceOf(NoConstructor::class, $o);
        $this->assertSame(42, $o->a());

        $o = ReflectionClass::of(WithConstructor::class)->build(Map::of(
            ['a', 24],
            ['b', 66],
        ));

        $this->assertInstanceOf(WithConstructor::class, $o);
        $this->assertSame(24, $o->a());
        $this->assertSame(66, $o->b());
    }

    public function testProperties()
    {
        $properties = ReflectionClass::of(WithConstructor::class)->properties();

        $this->assertInstanceOf(Set::class, $properties);
        $this->assertSame(['a', 'b'], $properties->toList());
    }
}
