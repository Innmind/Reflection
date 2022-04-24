<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\Instanciator;

use Innmind\Reflection\{
    Instanciator\ReflectionInstanciator,
    Instanciator,
    Exception\InstanciationFailed,
};
use Innmind\Immutable\{
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;

class ReflectionInstanciatorTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Instanciator::class,
            new ReflectionInstanciator,
        );
    }

    public function testBuild()
    {
        $i = new ReflectionInstanciator;

        $object = $i->build(
            Foo::class,
            Map::of(
                ['o', $o = new \stdClass],
                ['bar', 'foo'],
            ),
        );

        $this->assertInstanceOf(Foo::class, $object);
        $this->assertSame([$o, 'foo', null], $object->properties);

        $object = $i->build(
            Foo::class,
            Map::of(
                ['o', $o = new \stdClass],
                ['bar', 'foo'],
                ['baz', 42],
            ),
        );

        $this->assertInstanceOf(Foo::class, $object);
        $this->assertSame([$o, 'foo', 42], $object->properties);

        $object = $i->build('stdClass', Map::of());

        $this->assertInstanceOf('stdClass', $object);
    }

    public function testThrowWhenClassCannotBeInstanciated()
    {
        $i = new ReflectionInstanciator;

        $this->expectException(InstanciationFailed::class);
        $this->expectExceptionMessage('Class "Tests\Innmind\Reflection\Instanciator\Foo" cannot be instanciated');

        $i->build(Foo::class, Map::of());
    }

    public function testGetParameters()
    {
        $i = new ReflectionInstanciator;

        $parameters = $i->parameters(Foo::class);

        $this->assertInstanceOf(Set::class, $parameters);
        $this->assertSame(['o', 'bar', 'baz'], $parameters->toList());

        $parameters = $i->parameters('stdClass');

        $this->assertSame(0, $parameters->count());
    }
}

class Foo
{
    public $properties;

    public function __construct(\stdClass $o, string $bar, $baz = null)
    {
        $this->properties = [$o, $bar, $baz];
    }
}
