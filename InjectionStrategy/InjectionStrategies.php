<?php

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Immutable\TypedCollection;
use Innmind\Immutable\TypedCollectionInterface;
use Innmind\Reflection\Cache\StrategyCachingCapabilities;
use Innmind\Reflection\Exception\LogicException;

/**
 * DefaultInjectionStrategies
 *
 * @author Hugues Maignol <hugues@hmlb.fr>
 */
final class InjectionStrategies implements InjectionStrategiesInterface
{
    use StrategyCachingCapabilities;

    private $strategies;

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
