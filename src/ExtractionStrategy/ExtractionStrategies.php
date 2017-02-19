<?php

namespace Innmind\Reflection\ExtractionStrategy;

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
 * DefaultExtractionStrategies
 *
 * @author Hugues Maignol <hugues@hmlb.fr>
 */
final class ExtractionStrategies implements ExtractionStrategiesInterface
{
    use StrategyCachingCapabilities;

    private $strategies;

    public function __construct(SetInterface $strategies = null)
    {
        $this->strategies = $strategies ?? $this->all();

        if ((string) $this->strategies->type() !== ExtractionStrategyInterface::class) {
            throw new InvalidArgumentException;
        }
    }

    public function all(): SetInterface
    {
        if ($this->strategies === null) {
            return $this->strategies = (new Set(ExtractionStrategyInterface::class))
                ->add(new GetterStrategy)
                ->add(new NamedMethodStrategy)
                ->add(new IsserStrategy)
                ->add(new HasserStrategy)
                ->add(new ReflectionStrategy);
        }

        return $this->strategies;
    }

    public function get($object, string $key): ExtractionStrategyInterface
    {
        $strategy = $this->getCachedStrategy(get_class($object), $key);

        if ($strategy !== null) {
            return $strategy;
        }

        foreach ($this->all() as $strategy) {
            if ($strategy->supports($object, $key)) {

                $this->setCachedStrategy(get_class($object), $key, $strategy);

                return $strategy;
            }
        }

        throw new LogicException(
            sprintf(
                'Property "%s" cannot be extracted',
                $key
            )
        );
    }

}
