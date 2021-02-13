<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\Instanciator;

use Innmind\Reflection\{
    Instanciator\ConstructorLessInstanciator,
    Instanciator,
};
use Innmind\Immutable\Map;
use function Innmind\Immutable\unwrap;
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
                Map::of('string', 'variable')
            )
        );

        $builtObject = $instanciator->build(
            \get_class($object),
            Map::of('string', 'variable')
        );

        $this->assertInstanceOf(\get_class($object), $builtObject);
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
            unwrap($instanciator->parameters('stdClass')),
        );
        $this->assertSame(
            [],
            unwrap($instanciator->parameters(\get_class($object))),
        );
    }
}
