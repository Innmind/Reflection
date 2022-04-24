<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategy;

final class ExtractionStrategies
{
    public static function default(): ExtractionStrategy
    {
        return new DelegationStrategy(
            new GetterStrategy,
            new NamedMethodStrategy,
            new IsserStrategy,
            new HasserStrategy,
            new ReflectionStrategy,
        );
    }
}
