<?php

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategyInterface;

final class ExtractionStrategies
{
    private static $default;

    public static function default(): ExtractionStrategyInterface
    {
        if (self::$default == null) {
            self::$default = new DelegationStrategy(
                new GetterStrategy,
                new NamedMethodStrategy,
                new IsserStrategy,
                new HasserStrategy,
                new ReflectionStrategy
            );
        }

        return self::$default;
    }
}
