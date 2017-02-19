<?php

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\{
    Cache\StrategyCachingCapabilities,
    Exception\LogicException,
    Exception\InvalidArgumentException
};
use Innmind\Immutable\{
    SetInterface,
    Set
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

    public function __construct(SetInterface $strategies = null)
    {
        $this->strategies = $strategies ?? $this->all();

        if ((string) $this->strategies->type() !== InjectionStrategyInterface::class) {
            throw new InvalidArgumentException;
        }
    }

    public function all(): SetInterface
    {
        if ($this->strategies === null) {
            return $this->strategies = (new Set(InjectionStrategyInterface::class))
                ->add(new SetterStrategy)
                ->add(new NamedMethodStrategy)
                ->add(new ReflectionStrategy);
        }

        return $this->strategies;
    }

    public function get($object, string $key, $value): InjectionStrategyInterface
    {
        $strategy = $this->getCachedStrategy(get_class($object), $key);

        if ($strategy !== null) {
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
