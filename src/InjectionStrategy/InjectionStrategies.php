<?php

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\{
    Cache\StrategyCachingCapabilities,
    Exception\LogicException,
    Exception\InvalidArgumentException
};
use Innmind\Immutable\{
    TypedCollection,
    TypedCollectionInterface
};

/**
 * DefaultInjectionStrategies
 *
 * @author Hugues Maignol <hugues@hmlb.fr>
 */
final class InjectionStrategies implements InjectionStrategiesInterface
{
    use StrategyCachingCapabilities;

    private $strategies;

    public function __construct(TypedCollectionInterface $strategies = null)
    {
        $this->strategies = $strategies ?? $this->all();

        if ($this->strategies->getType() !== InjectionStrategyInterface::class) {
            throw new InvalidArgumentException;
        }
    }

    public function all(): TypedCollectionInterface
    {
        if ($this->strategies === null) {
            return $this->strategies = new TypedCollection(
                InjectionStrategyInterface::class,
                [
                    new SetterStrategy,
                    new NamedMethodStrategy,
                    new ReflectionStrategy,
                ]
            );
        }

        return $this->strategies;
    }

    public function get($object, string $key, $value): InjectionStrategyInterface
    {
        $strategy = $this->getCachedStrategy(get_class($object), $key);
        if (null !== $strategy) {
            return $strategy;
        }

        foreach ($this->all() as $strategy) {
            if ($strategy->supports($object, $key, $value)) {

                $this->setCachedStrategy(get_class($object), $key, $strategy);

                return $strategy;
            }
        }

        throw new LogicException(
            sprintf(
                'Property "%s" cannot be injected',
                $key
            )
        );
    }
}
