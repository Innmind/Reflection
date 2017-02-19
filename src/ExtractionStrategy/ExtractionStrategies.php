<?php

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategyInterface;
use Innmind\Immutable\Stream;

final class ExtractionStrategies
{
    private static $default;

    public static function default(): ExtractionStrategyInterface
    {
        if (self::$default == null) {
            self::$default = new DelegationStrategy(
                (new Stream(ExtractionStrategyInterface::class))
                    ->add(new GetterStrategy)
                    ->add(new NamedMethodStrategy)
                    ->add(new IsserStrategy)
                    ->add(new HasserStrategy)
                    ->add(new ReflectionStrategy)
            );
        }

        return self::$default;
    }
}
