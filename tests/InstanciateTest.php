<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection;

use Innmind\Reflection\Instanciate;
use Innmind\Immutable\Map;
use Innmind\BlackBox\{
    PHPUnit\Framework\TestCase,
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Reflection\{
    NoConstructor,
    ManyTypes,
};

class InstanciateTest extends TestCase
{
    use BlackBox;

    public function testInvoke(): BlackBox\Proof
    {
        return $this
            ->forAll(Set::type())
            ->prove(function($value) {
                $instanciate = new Instanciate;

                $object = $instanciate(NoConstructor::class, Map::of(['a', $value]))->match(
                    static fn($object) => $object,
                    static fn() => null,
                );

                $this->assertInstanceOf(NoConstructor::class, $object);
                $this->assertSame($value, $object->a());
            });
    }

    public function testPartialInstance(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Set::integers(),
                Set::of(true, false),
            )
            ->prove(function($value, $bool) {
                $instanciate = new Instanciate;

                $object = $instanciate(ManyTypes::class, Map::of(['a', $value], ['d', $bool]))->match(
                    static fn($object) => $object,
                    static fn() => null,
                );

                $this->assertInstanceOf(ManyTypes::class, $object);
                $this->assertSame($value, $object->a());
                $this->assertSame($bool, $object->d());

                try {
                    $object->b();
                    $this->fail('it should throw');
                } catch (\Error $e) {
                    $this->assertSame(
                        'Typed property Fixtures\Innmind\Reflection\ManyTypes::$b must not be accessed before initialization',
                        $e->getMessage(),
                    );
                }
            });
    }

    public function testReturnNothingWhenFailingToInjectAProperty(): BlackBox\Proof
    {
        return $this
            ->forAll(
                Set::integers(),
                Set::type(),
            )
            ->filter(static fn($value, $invalid) => !\is_bool($invalid))
            ->prove(function($value, $invalid) {
                $instanciate = new Instanciate;

                $object = $instanciate(ManyTypes::class, Map::of(['a', $value], ['d', $invalid]))->match(
                    static fn($object) => $object,
                    static fn() => null,
                );

                $this->assertNull($object);
            });
    }
}
