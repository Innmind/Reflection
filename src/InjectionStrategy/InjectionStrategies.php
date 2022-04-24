<?php
declare(strict_types = 1);

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\InjectionStrategy;

final class InjectionStrategies
{
    public static function default(): InjectionStrategy
    {
        return new DelegationStrategy(
            new SetterStrategy,
            new NamedMethodStrategy,
            new ReflectionStrategy,
        );
    }
}
