<?php
declare(strict_types = 1);

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\InjectionStrategy;

final class InjectionStrategies
{
    private static ?InjectionStrategy $default;

    public static function default(): InjectionStrategy
    {
        return self::$default ?? self::$default = new DelegationStrategy(
            new SetterStrategy,
            new NamedMethodStrategy,
            new ReflectionStrategy,
        );
    }
}
