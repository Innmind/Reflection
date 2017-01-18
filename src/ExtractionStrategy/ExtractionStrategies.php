<?php

namespace Innmind\Reflection\ExtractionStrategy;

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
 * DefaultExtractionStrategies
 *
 * @author Hugues Maignol <hugues@hmlb.fr>
 */
final class ExtractionStrategies implements ExtractionStrategiesInterface
{
    use StrategyCachingCapabilities;

    private $strategies;

    public function __construct(TypedCollectionInterface $strategies = null)
    {
        $this->strategies = $strategies ?? $this->all();

        if ($this->strategies->getType() !== ExtractionStrategyInterface::class) {
            throw new InvalidArgumentException;
        }
    }

    public function all(): TypedCollectionInterface
    {
        if ($this->strategies === null) {
            return $this->strategies = new TypedCollection(
                ExtractionStrategyInterface::class,
                [
                    new GetterStrategy,
                    new NamedMethodStrategy,
                    new IsserStrategy,
                    new HasserStrategy,
                    new ReflectionStrategy,
                ]
            );
        }

        return $this->strategies;
    }

    public function get($object, string $key): ExtractionStrategyInterface
    {
        $strategy = $this->getCachedStrategy(get_class($object), $key);
        if (null !== $strategy) {
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
