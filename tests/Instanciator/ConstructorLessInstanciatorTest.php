<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\Instanciator;

use Innmind\Reflection\{
    InstanciatorInterface,
    Instanciator\ConstructorLessInstanciator
};
use Innmind\Immutable\Collection;
use PHPUnit\Framework\TestCase;

class ConstructorLessInstanciatorTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            InstanciatorInterface::class,
            new ConstructorLessInstanciator
        );
    }

    public function testBuild()
    {
        $instanciator = new ConstructorLessInstanciator;
        $object = new class('bar') {
            public $foo;

            public function __construct(string $foo)
            {
                $this->foo = $foo;
            }
        };

        $this->assertInstanceOf(
            'stdClass',
            $instanciator->build(
                'stdClass',
                new Collection([])
            )
        );

        $builtObject = $instanciator->build(
            get_class($object),
            new Collection([])
        );

        $this->assertInstanceOf(get_class($object), $builtObject);
        $this->assertNull($builtObject->foo);
    }

    public function testGetParameters()
    {
        $instanciator = new ConstructorLessInstanciator;
        $object = new class('bar') {
            public function __construct(string $foo)
            {
            }
        };

        $this->assertSame(
            [],
            $instanciator->getParameters('stdClass')->toPrimitive()
        );
        $this->assertSame(
            [],
            $instanciator->getParameters(get_class($object))->toPrimitive()
        );
    }
}
