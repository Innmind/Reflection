<?php

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Immutable\TypedCollection;
use Innmind\Immutable\TypedCollectionInterface;
use Innmind\Reflection\Cache\StrategyCachingCapabilities;
use Innmind\Reflection\Exception\LogicException;

/**
 * DefaultExtractionStrategies
 *
 * @author Hugues Maignol <hugues@hmlb.fr>
 */
final class DefaultExtractionStrategies implements ExtractionStrategies
{

    use StrategyCachingCapabilities;

    private $strategies;

    public function all(): TypedCollectionInterface
    {
        if ($this->strategies == null) {
            return $this->strategies = new TypedCollection(
                ExtractionStrategyInterface::class,
                [
                    new GetterStrategy,
                    new NamedMethodStrategy,
                    new IsserStrategy,
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
