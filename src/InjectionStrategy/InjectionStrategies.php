<?php

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\InjectionStrategyInterface;

final class InjectionStrategies
{
    private static $default;

    public static function default(): InjectionStrategyInterface
    {
        if (self::$default === null) {
            self::$default = new DelegationStrategy(
                new SetterStrategy,
                new NamedMethodStrategy,
                new ReflectionStrategy
            );
        }

        return self::$default;
    }
}
