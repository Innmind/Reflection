<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Immutable\TypedCollection;
use Innmind\Immutable\TypedCollectionInterface;

final class ExtractionStrategies
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
            ExtractionStrategyInterface::class,
            [
                new GetterStrategy,
                new NamedMethodStrategy,
                new ReflectionStrategy,
            ]
        );

        return self::$defaults;
    }
}
