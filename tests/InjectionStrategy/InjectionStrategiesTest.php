<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\InjectionStrategy\{
    InjectionStrategies,
    DelegationStrategy,
    ReflectionStrategy,
};
use PHPUnit\Framework\TestCase;

class InjectionStrategiesTest extends TestCase
{
    public function testDefault()
    {
        $this->assertInstanceOf(
            ReflectionStrategy::class,
            InjectionStrategies::default(),
        );
    }

    public function testAll()
    {
        $this->assertInstanceOf(
            DelegationStrategy::class,
            InjectionStrategies::all(),
        );
        $this->assertEquals(
            InjectionStrategies::all(),
            InjectionStrategies::all(),
        );
    }
}
