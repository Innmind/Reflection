<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategy\{
    ExtractionStrategies,
    DelegationStrategy,
    ReflectionStrategy,
};
use PHPUnit\Framework\TestCase;

class ExtractionStrategiesTest extends TestCase
{
    public function testDefault()
    {
        $this->assertInstanceOf(
            ReflectionStrategy::class,
            ExtractionStrategies::default(),
        );
    }

    public function testAll()
    {
        $this->assertInstanceOf(
            DelegationStrategy::class,
            ExtractionStrategies::all(),
        );
        $this->assertEquals(
            ExtractionStrategies::all(),
            ExtractionStrategies::all(),
        );
    }
}
