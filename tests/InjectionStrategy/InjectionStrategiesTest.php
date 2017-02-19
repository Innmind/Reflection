<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\InjectionStrategy\InjectionStrategies;
use Innmind\Reflection\InjectionStrategy\InjectionStrategiesInterface;
use Innmind\Reflection\InjectionStrategyInterface;
use Innmind\Reflection\InjectionStrategy\SetterStrategy;
use Innmind\Reflection\InjectionStrategy\NamedMethodStrategy;
use Innmind\Reflection\InjectionStrategy\ReflectionStrategy;
use Innmind\Immutable\{
    SetInterface,
    Set
};
use PHPUnit\Framework\TestCase;

class InjectionStrategiesTest extends TestCase
{
    public function testDefaults()
    {
        $defaults = (new InjectionStrategies())->all();

        $this->assertInstanceOf(InjectionStrategiesInterface::class, new InjectionStrategies);
        $this->assertInstanceOf(SetInterface::class, $defaults);
        $this->assertSame(InjectionStrategyInterface::class, (string) $defaults->type());
        $defaults = $defaults->toPrimitive();
        $this->assertInstanceOf(SetterStrategy::class, $defaults[0]);
        $this->assertInstanceOf(NamedMethodStrategy::class, $defaults[1]);
        $this->assertInstanceOf(ReflectionStrategy::class, $defaults[2]);
    }

    public function testCustomStrategies()
    {
        $expected = (new Set(InjectionStrategyInterface::class))
            ->add(new ReflectionStrategy);
        $strategies = (new InjectionStrategies($expected))->all();

        $this->assertSame($expected, $strategies);
    }

    /**
     * @expectedException Innmind\Reflection\Exception\InvalidArgumentException
     */
    public function testThrowWhenInjectingInvalidCollection()
    {
        new InjectionStrategies(
            new Set('stdClass')
        );
    }
}
