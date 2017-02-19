<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\Instanciator;

use Innmind\Reflection\Instanciator\ReflectionInstanciator;
use Innmind\Immutable\{
    MapInterface,
    Map,
    SetInterface
};
use PHPUnit\Framework\TestCase;

class ReflectionInstanciatorTest extends TestCase
{
    public function testBuild()
    {
        $i = new ReflectionInstanciator;

        $object = $i->build(
            Foo::class,
            (new Map('string', 'mixed'))
                ->put('o', $o = new \stdClass)
                ->put('bar', 'foo')
        );

        $this->assertInstanceOf(Foo::class, $object);
        $this->assertSame([$o, 'foo', null], $object->properties);

        $object = $i->build(
            Foo::class,
            (new Map('string', 'mixed'))
                ->put('o', $o = new \stdClass)
                ->put('bar', 'foo')
                ->put('baz', 42)
        );

        $this->assertInstanceOf(Foo::class, $object);
        $this->assertSame([$o, 'foo', 42], $object->properties);

        $object = $i->build('stdClass', new Map('string', 'mixed'));

        $this->assertInstanceOf('stdClass', $object);
    }

    /**
     * @expectedException Innmind\Reflection\Exception\InstanciationFailedException
     * @expectedExceptionMessage Class "Tests\Innmind\Reflection\Instanciator\Foo" cannot be instanciated
     */
    public function testThrowWhenClassCannotBeInstanciated()
    {
        $i = new ReflectionInstanciator;

        $i->build(Foo::class, new Map('string', 'mixed'));
    }

    public function testGetParameters()
    {
        $i = new ReflectionInstanciator;

        $parameters = $i->parameters(Foo::class);

        $this->assertInstanceOf(SetInterface::class, $parameters);
        $this->assertSame('string', (string) $parameters->type());
        $this->assertSame(['o', 'bar', 'baz'], $parameters->toPrimitive());

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
