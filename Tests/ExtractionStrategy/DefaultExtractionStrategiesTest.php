<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Tests\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategy\DefaultExtractionStrategies;
use Innmind\Reflection\ExtractionStrategy\ExtractionStrategyInterface;
use Innmind\Reflection\ExtractionStrategy\GetterStrategy;
use Innmind\Reflection\ExtractionStrategy\NamedMethodStrategy;
use Innmind\Reflection\ExtractionStrategy\ReflectionStrategy;
use Innmind\Immutable\TypedCollection;

class DefaultExtractionStrategiesTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $defaults = (new DefaultExtractionStrategies())->all();

        $this->assertInstanceOf(TypedCollection::class, $defaults);
        $this->assertSame(ExtractionStrategyInterface::class, $defaults->getType());
        $this->assertInstanceOf(GetterStrategy::class, $defaults[0]);
        $this->assertInstanceOf(NamedMethodStrategy::class, $defaults[1]);
        $this->assertInstanceOf(ReflectionStrategy::class, $defaults[2]);
    }
}
