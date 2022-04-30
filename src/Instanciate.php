<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Immutable\{
    Map,
    Maybe,
    Set,
};

final class Instanciate
{
    /**
     * @template T of object
     *
     * @param class-string<T> $class
     * @param Map<non-empty-string, mixed> $properties
     *
     * @return Maybe<T>
     */
    public function __invoke(string $class, Map $properties): Maybe
    {
        try {
            $object = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        } catch (\Throwable $e) {
            /** @var Maybe<T> */
            return Maybe::nothing();
        }

        $reflection = ReflectionClass::of($class)->properties();

        /**
         * @psalm-suppress InvalidArgument For some reason it doesn't understand the A template
         * @psalm-suppress MixedArgumentTypeCoercion
         * @var Maybe<T>
         */
        return $properties->reduce(
            Maybe::just($object),
            fn(Maybe $object, $property, $value) => $this->inject(
                $object,
                $property,
                $value,
                $reflection,
            ),
        );
    }

    /**
     * @template A of object
     *
     * @param Maybe<A> $object
     * @param non-empty-string $property
     * @param Set<ReflectionProperty<A>> $properties
     *
     * @return Maybe<A>
     */
    private function inject(
        Maybe $object,
        string $property,
        mixed $value,
        Set $properties,
    ): Maybe {
        /** @var Maybe<A> */
        return Maybe::all(
            $object,
            $properties->find(static fn($prop) => $prop->name() === $property),
        )->flatMap(
            static fn(object $object, ReflectionProperty $property) => $property->inject(
                $object,
                $value,
            ),
        );
    }
}
