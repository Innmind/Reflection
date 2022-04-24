<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\Visitor;

use Innmind\Reflection\{
    Visitor\AccessProperty,
    Exception\PropertyNotFound,
};
use Fixtures\Innmind\Reflection\Foo;
use PHPUnit\Framework\TestCase;

class AccessPropertyTest extends TestCase
{
    /**
     * @dataProvider cases
     */
    public function testAccessProperty($object, string $property)
    {
        $this->assertInstanceOf(
            \ReflectionProperty::class,
            (new AccessProperty)($object, $property),
        );
    }

    public function testThrowWhenPropertyNotFound()
    {
        $this->expectException(PropertyNotFound::class);
        $this->expectExceptionMessage('foo');

        (new AccessProperty)(new \stdClass, 'foo');
    }

    public function cases(): array
    {
        return [
            [
                new class {
                    public $foo;
                },
                'foo',
            ],
            [
                new class {
                    protected $foo;
                },
                'foo',
            ],
            [
                new class {
                    private $foo;
                },
                'foo',
            ],
            [
                new class extends Foo {
                },
                'someProperty',
            ],
            [
                new class extends Foo {
                },
                'inheritProtected',
            ],
        ];
    }
}
