<?php

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\InjectionStrategyInterface;
use Innmind\Immutable\Stream;

final class InjectionStrategies
{
    private static $default;

    public static function default(): InjectionStrategyInterface
    {
        if (self::$default === null) {
            self::$default = new DelegationStrategy(
                (new Stream(InjectionStrategyInterface::class))
                    ->add(new SetterStrategy)
                    ->add(new NamedMethodStrategy)
                    ->add(new ReflectionStrategy)
            );
        }

        return self::$default;
    }
}
