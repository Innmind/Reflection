<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\Instanciator;

use Innmind\Reflection\{
    Instanciator,
    Instanciator\ConstructorLessInstanciator
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class ConstructorLessInstanciatorTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Instanciator::class,
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
                new Map('string', 'variable')
            )
        );

        $builtObject = $instanciator->build(
            get_class($object),
            new Map('string', 'variable')
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
            $instanciator->parameters('stdClass')->toPrimitive()
        );
        $this->assertSame(
            [],
            $instanciator->parameters(get_class($object))->toPrimitive()
        );
    }
}
