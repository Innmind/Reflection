<?php
declare(strict_types = 1);

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\{
    InjectionStrategy,
    Exception\InvalidArgumentException,
    Exception\PropertyCannotBeInjected,
};
use Innmind\Immutable\{
    Sequence,
    Map,
};

final class DelegationStrategy implements InjectionStrategy
{
    /** @var Sequence<InjectionStrategy<object>> */
    private Sequence $strategies;
    /** @var Map<string, InjectionStrategy<object>> */
    private Map $cache;

    /**
     * @no-named-arguments
     */
    public function __construct(InjectionStrategy ...$strategies)
    {
        $this->strategies = Sequence::of(...$strategies);
        /** @var Map<string, InjectionStrategy<object>> */
        $this->cache = Map::of();
    }

    public function supports(object $object, string $property, $value): bool
    {
        return $this->strategies->any(
            static fn($strategy) => $strategy->supports($object, $property, $value),
        );
    }

    public function inject(object $object, string $property, $value): object
    {
        $key = $this->generateKey($object, $property);

        $strategy = $this
            ->cache
            ->get($key)
            ->otherwise(
                fn() => $this->strategies->find(
                    static fn($strategy) => $strategy->supports($object, $property, $value),
                ),
            )
            ->match(
                static fn($strategy) => $strategy,
                static fn() => throw new PropertyCannotBeInjected($property),
            );

        $this->cache = ($this->cache)($key, $strategy);

        return $strategy->inject($object, $property, $value);
    }

    private function generateKey(object $object, string $property): string
    {
        return \get_class($object).'::'.$property;
    }
}
