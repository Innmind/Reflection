<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Tests\Instanciator;

use Innmind\Reflection\Instanciator\ReflectionInstanciator;
use Innmind\Immutable\Collection;
use Innmind\Immutable\CollectionInterface;

class ReflectionInstanciatorTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $i = new ReflectionInstanciator;

        $object = $i->build(
            Foo::class,
            new Collection([
                'o' => $o = new \stdClass,
                'bar' => 'foo',
            ])
        );

        $this->assertInstanceOf(Foo::class, $object);
        $this->assertSame([$o, 'foo', null], $object->properties);

        $object = $i->build(
            Foo::class,
            new Collection([
                'o' => $o = new \stdClass,
                'bar' => 'foo',
                'baz' => 42,
            ])
        );

        $this->assertInstanceOf(Foo::class, $object);
        $this->assertSame([$o, 'foo', 42], $object->properties);
    }

    /**
     * @expectedException Innmind\Reflection\Exception\InstanciationFailedException
     * @expectedExceptionMessage Class "Innmind\Reflection\Tests\Instanciator\Foo" cannot be instanciated
     */
    public function testThrowWhenClassCannotBeInstanciated()
    {
        $i = new ReflectionInstanciator;

        $i->build(Foo::class, new Collection([]));
    }

    public function testGetParameters()
    {
        $i = new ReflectionInstanciator;

        $parameters = $i->getParameters(Foo::class);

        $this->assertInstanceOf(CollectionInterface::class, $parameters);
        $this->assertSame(['o', 'bar', 'baz'], $parameters->toPrimitive());
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
