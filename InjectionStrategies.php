<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\InjectionStrategy\SetterStrategy;
use Innmind\Reflection\InjectionStrategy\NamedMethodStrategy;
use Innmind\Reflection\InjectionStrategy\ReflectionStrategy;
use Innmind\Immutable\TypedCollection;

final class InjectionStrategies
{
    private static $defaults;

    /**
     * Return a collection of the default strategies available
     *
     * @return TypedCollection
     */
    public static function defaults(): TypedCollection
    {
        if (self::$defaults !== null) {
            return self::$defaults;
        }

        self::$defaults = new TypedCollection(
            InjectionStrategyInterface::class,
            [
                new SetterStrategy,
                new NamedMethodStrategy,
                new ReflectionStrategy,
            ]
        );

        return self::$defaults;
    }
}
