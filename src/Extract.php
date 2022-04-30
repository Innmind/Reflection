<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Immutable\{
    Map,
    Maybe,
    Set,
};

final class Extract
{
    /**
     * @no-named-arguments
     * @template T of object
     *
     * @param T $object
     * @param Set<non-empty-string> $properties
     *
     * @return Maybe<Map<non-empty-string, mixed>> Returns nothing when not all properties can be extracted
     */
    public function __invoke(object $object, Set $properties): Maybe
    {
        $props = ReflectionClass::of($object::class)->properties();
        /** @var Maybe<Map<non-empty-string, mixed>> */
        $values = Maybe::just(Map::of());

        /**
         * @psalm-suppress MixedArgumentTypeCoercion
         * @var Maybe<Map<non-empty-string, mixed>>
         */
        return $properties->reduce(
            $values,
            static fn(Maybe $values, string $property) => $props
                ->find(static fn($prop) => $prop->name() === $property)
                ->flatMap(
                    static fn($property) => $values->map(
                        static fn(Map $values) => ($values)(
                            $property->name(),
                            $property->extract($object),
                        ),
                    ),
                ),
        );
    }
}
