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
use function Innmind\Immutable\unwrap;
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
            Map::of('string', 'mixed')
                ('o', $o = new \stdClass)
                ('bar', 'foo'),
        );

        $this->assertInstanceOf(Foo::class, $object);
        $this->assertSame([$o, 'foo', null], $object->properties);

        $object = $i->build(
            Foo::class,
            Map::of('string', 'mixed')
                ('o', $o = new \stdClass)
                ('bar', 'foo')
                ('baz', 42),
        );

        $this->assertInstanceOf(Foo::class, $object);
        $this->assertSame([$o, 'foo', 42], $object->properties);

        $object = $i->build('stdClass', Map::of('string', 'mixed'));

        $this->assertInstanceOf('stdClass', $object);
    }

    public function testThrowWhenClassCannotBeInstanciated()
    {
        $i = new ReflectionInstanciator;

        $this->expectException(InstanciationFailed::class);
        $this->expectExceptionMessage('Class "Tests\Innmind\Reflection\Instanciator\Foo" cannot be instanciated');

        $i->build(Foo::class, Map::of('string', 'mixed'));
    }

    public function testGetParameters()
    {
        $i = new ReflectionInstanciator;

        $parameters = $i->parameters(Foo::class);

        $this->assertInstanceOf(Set::class, $parameters);
        $this->assertSame('string', (string) $parameters->type());
        $this->assertSame(['o', 'bar', 'baz'], unwrap($parameters));

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
