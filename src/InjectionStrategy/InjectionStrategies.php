<?php

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\InjectionStrategy;

final class InjectionStrategies
{
    private static $default;

    public static function default(): InjectionStrategy
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
