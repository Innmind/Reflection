<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection;

use Innmind\Reflection\{
    Extract,
    Instanciate,
};
use Innmind\Immutable\{
    Map,
    Set,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set as DataSet,
};
use Fixtures\Innmind\Reflection\WithConstructor;

class ExtractTest extends TestCase
{
    use BlackBox;

    public function testExtract()
    {
        $this
            ->forAll(
                DataSet\AnyType::any(),
                DataSet\AnyType::any(),
            )
            ->then(function($a, $b) {
                $instanciate = new Instanciate;

                $object = $instanciate(WithConstructor::class, Map::of(['a', $a], ['b', $b]))->match(
                    static fn($object) => $object,
                    static fn() => null,
                );

                $this->assertInstanceOf(WithConstructor::class, $object);

                $extract = new Extract;

                $values = $extract($object, Set::of('a', 'b'))->match(
                    static fn($values) => $values,
                    static fn() => null,
                );

                $this->assertInstanceOf(Map::class, $values);
                $this->assertSame($a, $values->get('a')->match(
                    static fn($value) => $value,
                    static fn() => null,
                ));
                $this->assertSame($b, $values->get('b')->match(
                    static fn($value) => $value,
                    static fn() => null,
                ));
            });
    }

    public function testReturnNothingWhenFailingToExtractAProperty()
    {
        $this
            ->forAll(
                DataSet\AnyType::any(),
                DataSet\AnyType::any(),
            )
            ->then(function($a, $b) {
                $instanciate = new Instanciate;

                $object = $instanciate(WithConstructor::class, Map::of(['a', $a], ['b', $b]))->match(
                    static fn($object) => $object,
                    static fn() => null,
                );

                $this->assertInstanceOf(WithConstructor::class, $object);

                $extract = new Extract;

                $values = $extract($object, Set::of('a', 'b', 'c'))->match(
                    static fn($values) => $values,
                    static fn() => null,
                );

                $this->assertNull($values);
            });
    }
}
