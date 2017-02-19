<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\InjectionStrategy\{
    InjectionStrategies,
    DelegationStrategy
};
use PHPUnit\Framework\TestCase;

class InjectionStrategiesTest extends TestCase
{
    public function testDefault()
    {
        $this->assertInstanceOf(
            DelegationStrategy::class,
            InjectionStrategies::default()
        );
        $this->assertSame(
            InjectionStrategies::default(),
            InjectionStrategies::default()
        );
    }
}
