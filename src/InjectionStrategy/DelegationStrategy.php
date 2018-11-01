<?php
declare(strict_types = 1);

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\{
    InjectionStrategyInterface,
    Exception\InvalidArgumentException,
    Exception\PropertyCannotBeInjectedException
};
use Innmind\Immutable\{
    Stream,
    Map
};

final class DelegationStrategy implements InjectionStrategyInterface
{
    private $strategies;
    private $cache;

    public function __construct(InjectionStrategyInterface ...$strategies)
    {
        $this->strategies = Stream::of(InjectionStrategyInterface::class, ...$strategies);
        $this->cache = new Map('string', InjectionStrategyInterface::class);
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
                function(bool $supports, InjectionStrategyInterface $strategy) use ($object, $property, $value): bool {
                    if ($supports === true) {
                        return true;
                    }

                    return $strategy->supports($object, $property, $value);
                }
            );
    }

    /**
     * {@inheritdoc}
     */
    public function inject(object $object, string $property, $value): void
    {
        $key = $this->generateKey($object, $property);

        if ($this->cache->contains($key)) {
            $this
                ->cache
                ->get($key)
                ->inject($object, $property, $value);

            return;
        }

        $strategy = $this->strategies->reduce(
            null,
            function($target, InjectionStrategyInterface $strategy) use ($object, $property, $value) {
                if ($target instanceof InjectionStrategyInterface) {
                    return $target;
                }

                if ($strategy->supports($object, $property, $value)) {
                    return $strategy;
                }
            }
        );

        if (!$strategy instanceof InjectionStrategyInterface) {
            throw new PropertyCannotBeInjectedException($property);
        }

        $this->cache = $this->cache->put($key, $strategy);

        $strategy->inject($object, $property, $value);
    }

    private function generateKey(object $object, string $property): string
    {
        return get_class($object).'::'.$property;
    }
}
