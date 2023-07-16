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
    ManyTypes,
    Attr,
};
use Innmind\Immutable\{
    Set,
    Map,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set as DataSet,
};

class ReflectionClassTest extends TestCase
{
    use BlackBox;

    public function testProperties()
    {
        $properties = ReflectionClass::of(WithConstructor::class)->properties();

        $this->assertInstanceOf(Set::class, $properties);
        $this->assertSame(
            ['a', 'b'],
            $properties
                ->map(static fn($property) => $property->name())
                ->toList(),
        );
    }

    public function testPropertiesType()
    {
        $properties = ReflectionClass::of(ManyTypes::class)->properties();
        $types = $properties->map(static fn($property) => $property->type());

        $this->assertSame(
            [
                'int',
                'float',
                'string',
                'bool',
                'array',
                'object',
                \Closure::class,
                NoConstructor::class,
                'mixed',
                '?'.NoConstructor::class,
                'string|int',
                'Countable&ArrayAccess',
            ],
            $types
                ->map(static fn($type) => $type->toString())
                ->toList(),
        );

        $this->assertSame(
            'mixed',
            $properties
                ->find(static fn($property) => $property->name() === 'j')
                ->map(static fn($property) => $property->type()->toString())
                ->match(
                    static fn($type) => $type,
                    static fn() => null,
                ),
        );

        $types = $properties
            ->map(static fn($property) => [$property->name(), $property->type()])
            ->toList();
        $types = \array_combine(\array_column($types, 0), \array_column($types, 1));

        $this
            ->forAll(DataSet\Integers::any())
            ->then(function($int) use ($types) {
                $this->assertTrue($types['a']->allows($int));
            });
        $this
            ->forAll(DataSet\Type::any())
            ->then(function($type) use ($types) {
                if (\is_int($type)) {
                    return;
                }

                $this->assertFalse($types['a']->allows($type));
            });
        $this
            ->forAll(DataSet\RealNumbers::any())
            ->then(function($value) use ($types) {
                if (\is_int($value)) {
                    return;
                }

                $this->assertTrue($types['b']->allows($value));
            });
        $this
            ->forAll(DataSet\Type::any())
            ->then(function($type) use ($types) {
                if (\is_float($type)) {
                    return;
                }

                $this->assertFalse($types['b']->allows($type));
            });
        $this
            ->forAll(DataSet\Unicode::strings())
            ->then(function($value) use ($types) {
                $this->assertTrue($types['c']->allows($value));
            });
        $this
            ->forAll(DataSet\Type::any())
            ->then(function($type) use ($types) {
                if (\is_string($type)) {
                    return;
                }

                $this->assertFalse($types['c']->allows($type));
            });
        $this
            ->forAll(DataSet\Elements::of(true, false))
            ->then(function($value) use ($types) {
                $this->assertTrue($types['d']->allows($value));
            });
        $this
            ->forAll(DataSet\Type::any())
            ->then(function($type) use ($types) {
                if (\is_bool($type)) {
                    return;
                }

                $this->assertFalse($types['d']->allows($type));
            });
        $this
            ->forAll(DataSet\Sequence::of(DataSet\Type::any()))
            ->then(function($value) use ($types) {
                $this->assertTrue($types['e']->allows($value));
            });
        $this
            ->forAll(DataSet\Type::any())
            ->then(function($type) use ($types) {
                if (\is_array($type)) {
                    return;
                }

                $this->assertFalse($types['e']->allows($type));
            });
        $this->assertTrue($types['f']->allows(new class {
        }));
        $this->assertTrue($types['f']->allows(new \stdClass));
        $this
            ->forAll(DataSet\Type::any())
            ->then(function($type) use ($types) {
                if (\is_object($type)) {
                    return;
                }

                $this->assertFalse($types['f']->allows($type));
            });
        $this->assertTrue($types['g']->allows(static fn() => null));
        $this
            ->forAll(DataSet\Type::any())
            ->then(function($type) use ($types) {
                if (\is_callable($type)) {
                    return;
                }

                $this->assertFalse($types['g']->allows($type));
            });
        $this->assertTrue($types['h']->allows(new NoConstructor));
        $this
            ->forAll(DataSet\Type::any())
            ->then(function($type) use ($types) {
                $this->assertFalse($types['h']->allows($type));
            });
        $this
            ->forAll(DataSet\Type::any())
            ->then(function($type) use ($types) {
                $this->assertTrue($types['i']->allows($type));
                $this->assertTrue($types['j']->allows($type));
            });
        $this->assertTrue($types['k']->allows(new NoConstructor));
        $this->assertTrue($types['k']->allows(null));
        $this
            ->forAll(DataSet\Either::any(
                DataSet\Integers::any(),
                DataSet\Unicode::strings(),
            ))
            ->then(function($value) use ($types) {
                $this->assertTrue($types['union']->allows($value));
            });
        $this
            ->forAll(DataSet\Type::any())
            ->then(function($type) use ($types) {
                if (\is_int($type) || \is_string($type)) {
                    return;
                }

                $this->assertFalse($types['union']->allows($type));
            });
        $this->assertTrue($types['intersection']->allows(new \ArrayIterator([])));
        $this
            ->forAll(DataSet\Type::any())
            ->then(function($type) use ($types) {
                if ($type instanceof \Countable) {
                    return;
                }

                $this->assertFalse($types['intersection']->allows($type));
            });
    }

    public function testAttributes()
    {
        $properties = ReflectionClass::of(ManyTypes::class)->properties();

        $this->assertSame(
            'foo',
            $properties
                ->find(static fn($property) => $property->name() === 'a')
                ->map(static fn($property) => $property->attributes())
                ->flatMap(static fn($attributes) => $attributes->find(
                    static fn($attribute) => $attribute->class() === Attr::class,
                ))
                ->match(
                    static fn($attribute) => $attribute->instance()->value,
                    static fn() => null,
                ),
        );
        $this->assertSame(
            'bar',
            $properties
                ->find(static fn($property) => $property->name() === 'b')
                ->map(static fn($property) => $property->attributes())
                ->flatMap(static fn($attributes) => $attributes->find(
                    static fn($attribute) => $attribute->class() === Attr::class,
                ))
                ->match(
                    static fn($attribute) => $attribute->instance()->value,
                    static fn() => null,
                ),
        );
        $this->assertNull(
            $properties
                ->find(static fn($property) => $property->name() === 'c')
                ->map(static fn($property) => $property->attributes())
                ->flatMap(static fn($attributes) => $attributes->find(
                    static fn($attribute) => $attribute->class() === Attr::class,
                ))
                ->match(
                    static fn($attribute) => $attribute->instance(),
                    static fn() => null,
                ),
        );
    }
}
