<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\{
    ExtractionStrategy,
    Exception\InvalidArgumentException,
    Exception\PropertyCannotBeExtracted,
};
use Innmind\Immutable\{
    Sequence,
    Map,
};

final class DelegationStrategy implements ExtractionStrategy
{
    /** @var Sequence<ExtractionStrategy> */
    private Sequence $strategies;
    /** @var Map<string, ExtractionStrategy> */
    private Map $cache;

    /**
     * @no-named-arguments
     */
    public function __construct(ExtractionStrategy ...$strategies)
    {
        $this->strategies = Sequence::of(...$strategies);
        /** @var Map<string, ExtractionStrategy> */
        $this->cache = Map::of();
    }

    public function supports(object $object, string $property): bool
    {
        return $this->strategies->any(
            static fn($strategy) => $strategy->supports($object, $property),
        );
    }

    public function extract(object $object, string $property): mixed
    {
        $key = $this->generateKey($object, $property);

        $strategy = $this
            ->cache
            ->get($key)
            ->otherwise(
                fn() => $this->strategies->find(
                    static fn($strategy) => $strategy->supports($object, $property),
                ),
            )
            ->match(
                static fn($strategy) => $strategy,
                static fn() => throw new PropertyCannotBeExtracted($property),
            );

        $this->cache = ($this->cache)($key, $strategy);

        return $strategy->extract($object, $property);
    }

    private function generateKey(object $object, string $property): string
    {
        return \get_class($object).'::'.$property;
    }
}
