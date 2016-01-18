<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Tests;

use Innmind\Reflection\InjectionStrategy\InjectionStrategies;
use Innmind\Reflection\InjectionStrategy\InjectionStrategyInterface;
use Innmind\Reflection\InjectionStrategy\SetterStrategy;
use Innmind\Reflection\InjectionStrategy\NamedMethodStrategy;
use Innmind\Reflection\InjectionStrategy\ReflectionStrategy;
use Innmind\Immutable\TypedCollection;

class InjectionStrategiesTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $defaults = InjectionStrategies::defaults();

        $this->assertInstanceOf(TypedCollection::class, $defaults);
        $this->assertSame(InjectionStrategyInterface::class, $defaults->getType());
        $this->assertInstanceOf(SetterStrategy::class, $defaults[0]);
        $this->assertInstanceOf(NamedMethodStrategy::class, $defaults[1]);
        $this->assertInstanceOf(ReflectionStrategy::class, $defaults[2]);
        $this->assertSame($defaults, InjectionStrategies::defaults());
    }
}
