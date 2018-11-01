<?php

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategy;

final class ExtractionStrategies
{
    private static $default;

    public static function default(): ExtractionStrategy
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
