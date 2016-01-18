<?php
declare(strict_types = 1);

namespace Innmind\Reflection\InjectionStrategy;

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
