<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Tests\InjectionStrategy;

use Innmind\Reflection\InjectionStrategy\DefaultInjectionStrategies;
use Innmind\Reflection\InjectionStrategy\InjectionStrategyInterface;
use Innmind\Reflection\InjectionStrategy\SetterStrategy;
use Innmind\Reflection\InjectionStrategy\NamedMethodStrategy;
use Innmind\Reflection\InjectionStrategy\ReflectionStrategy;
use Innmind\Immutable\TypedCollection;

class DefaultInjectionStrategiesTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $defaults = (new DefaultInjectionStrategies())->all();

        $this->assertInstanceOf(TypedCollection::class, $defaults);
        $this->assertSame(InjectionStrategyInterface::class, $defaults->getType());
        $this->assertInstanceOf(SetterStrategy::class, $defaults[0]);
        $this->assertInstanceOf(NamedMethodStrategy::class, $defaults[1]);
        $this->assertInstanceOf(ReflectionStrategy::class, $defaults[2]);
    }
}
