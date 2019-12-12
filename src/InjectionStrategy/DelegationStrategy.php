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
    /** @var Sequence<InjectionStrategy> */
    private Sequence $strategies;
    /** @var Map<string, InjectionStrategy> */
    private Map $cache;

    public function __construct(InjectionStrategy ...$strategies)
    {
        /** @var Sequence<InjectionStrategy> */
        $this->strategies = Sequence::of(InjectionStrategy::class, ...$strategies);
        /** @var Map<string, InjectionStrategy> */
        $this->cache = Map::of('string', InjectionStrategy::class);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(object $object, string $property, $value): bool
    {
        return $this
            ->strategies
            ->reduce(
                false,
                function(bool $supports, InjectionStrategy $strategy) use ($object, $property, $value): bool {
                    return $supports || $strategy->supports($object, $property, $value);
                },
            );
    }

    /**
     * {@inheritdoc}
     */
    public function inject(object $object, string $property, $value): object
    {
        $key = $this->generateKey($object, $property);

        if ($this->cache->contains($key)) {
            return $this
                ->cache
                ->get($key)
                ->inject($object, $property, $value);
        }

        $strategy = $this->strategies->reduce(
            null,
            function(?InjectionStrategy $target, InjectionStrategy $strategy) use ($object, $property, $value): ?InjectionStrategy {
                return $target ?? ($strategy->supports($object, $property, $value) ? $strategy : null);
            },
        );

        if (!$strategy instanceof InjectionStrategy) {
            throw new PropertyCannotBeInjected($property);
        }

        $this->cache = ($this->cache)($key, $strategy);

        return $strategy->inject($object, $property, $value);
    }

    private function generateKey(object $object, string $property): string
    {
        return \get_class($object).'::'.$property;
    }
}
