<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategy\{
    ExtractionStrategies,
    DelegationStrategy,
};
use PHPUnit\Framework\TestCase;

class ExtractionStrategiesTest extends TestCase
{
    public function testDefault()
    {
        $this->assertInstanceOf(
            DelegationStrategy::class,
            ExtractionStrategies::default()
        );
        $this->assertSame(
            ExtractionStrategies::default(),
            ExtractionStrategies::default()
        );
    }
}
