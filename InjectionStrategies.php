<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\InjectionStrategy\SetterStrategy;
use Innmind\Reflection\InjectionStrategy\NamedMethodStrategy;
use Innmind\Reflection\InjectionStrategy\ReflectionStrategy;
use Innmind\Immutable\TypedCollection;
use Innmind\Immutable\TypedCollectionInterface;

final class InjectionStrategies
{
    private static $defaults;

    /**
     * Return a collection of the default strategies available
     *
     * @return TypedCollectionInterface
     */
    public static function defaults(): TypedCollectionInterface
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
