<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\{
    ExtractionStrategyInterface,
    Exception\InvalidArgumentException,
    Exception\PropertyCannotBeExtractedException
};
use Innmind\Immutable\{
    Stream,
    Map
};

final class DelegationStrategy implements ExtractionStrategyInterface
{
    private $strategies;
    private $cache;

    public function __construct(ExtractionStrategyInterface ...$strategies)
    {
        $this->strategies = Stream::of(ExtractionStrategyInterface::class, ...$strategies);
        $this->cache = new Map('string', ExtractionStrategyInterface::class);
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
                function(bool $supports, ExtractionStrategyInterface $strategy) use ($object, $property): bool {
                    if ($supports === true) {
                        return true;
                    }

                    return $strategy->supports($object, $property);
                }
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
            function($target, ExtractionStrategyInterface $strategy) use ($object, $property) {
                if ($target instanceof ExtractionStrategyInterface) {
                    return $target;
                }

                if ($strategy->supports($object, $property)) {
                    return $strategy;
                }
            }
        );

        if (!$strategy instanceof ExtractionStrategyInterface) {
            throw new PropertyCannotBeExtractedException($property);
        }

        $this->cache = $this->cache->put($key, $strategy);

        return $strategy->extract($object, $property);
    }

    private function generateKey(object $object, string $property): string
    {
        return get_class($object).'::'.$property;
    }
}
