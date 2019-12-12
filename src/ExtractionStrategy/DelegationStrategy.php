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
    private $strategies;
    private $cache;

    public function __construct(ExtractionStrategy ...$strategies)
    {
        $this->strategies = Sequence::of(ExtractionStrategy::class, ...$strategies);
        $this->cache = Map::of('string', ExtractionStrategy::class);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(object $object, string $property): bool
    {
        return $this
            ->strategies
            ->reduce(
                false,
                function(bool $supports, ExtractionStrategy $strategy) use ($object, $property): bool {
                    return $supports || $strategy->supports($object, $property);
                },
            );
    }

    /**
     * {@inheritdoc}
     */
    public function extract(object $object, string $property)
    {
        $key = $this->generateKey($object, $property);

        if ($this->cache->contains($key)) {
            return $this
                ->cache
                ->get($key)
                ->extract($object, $property);
        }

        $strategy = $this->strategies->reduce(
            null,
            function(?ExtractionStrategy $target, ExtractionStrategy $strategy) use ($object, $property): ?ExtractionStrategy {
                return $target ?? ($strategy->supports($object, $property) ? $strategy : null);
            },
        );

        if (!$strategy instanceof ExtractionStrategy) {
            throw new PropertyCannotBeExtracted($property);
        }

        $this->cache = ($this->cache)($key, $strategy);

        return $strategy->extract($object, $property);
    }

    private function generateKey(object $object, string $property): string
    {
        return \get_class($object).'::'.$property;
    }
}
