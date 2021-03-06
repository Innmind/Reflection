<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategy;

final class ExtractionStrategies
{
    private static ?ExtractionStrategy $default;

    public static function default(): ExtractionStrategy
    {
        return self::$default ?? self::$default = new DelegationStrategy(
            new GetterStrategy,
            new NamedMethodStrategy,
            new IsserStrategy,
            new HasserStrategy,
            new ReflectionStrategy,
        );
    }
}
