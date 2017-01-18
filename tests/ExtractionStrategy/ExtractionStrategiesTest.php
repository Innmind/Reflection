<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategy\ExtractionStrategies;
use Innmind\Reflection\ExtractionStrategy\ExtractionStrategiesInterface;
use Innmind\Reflection\ExtractionStrategy\ExtractionStrategyInterface;
use Innmind\Reflection\ExtractionStrategy\GetterStrategy;
use Innmind\Reflection\ExtractionStrategy\NamedMethodStrategy;
use Innmind\Reflection\ExtractionStrategy\ReflectionStrategy;
use Innmind\Reflection\ExtractionStrategy\IsserStrategy;
use Innmind\Reflection\ExtractionStrategy\HasserStrategy;
use Innmind\Immutable\TypedCollection;

class ExtractionStrategiesTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $defaults = (new ExtractionStrategies())->all();

        $this->assertInstanceOf(ExtractionStrategiesInterface::class, new ExtractionStrategies);
        $this->assertInstanceOf(TypedCollection::class, $defaults);
        $this->assertSame(ExtractionStrategyInterface::class, $defaults->getType());
        $this->assertInstanceOf(GetterStrategy::class, $defaults[0]);
        $this->assertInstanceOf(NamedMethodStrategy::class, $defaults[1]);
        $this->assertInstanceOf(IsserStrategy::class, $defaults[2]);
        $this->assertInstanceOf(HasserStrategy::class, $defaults[3]);
        $this->assertInstanceOf(ReflectionStrategy::class, $defaults[4]);
    }

    public function testCustomStrategies()
    {
        $expected = new TypedCollection(
            ExtractionStrategyInterface::class,
            [new ReflectionStrategy]
        );
        $strategies = (new ExtractionStrategies($expected))->all();

        $this->assertSame($expected, $strategies);
    }

    /**
     * @expectedException Innmind\Reflection\Exception\InvalidArgumentException
     */
    public function testThrowWhenInjectingInvalidCollection()
    {
        new ExtractionStrategies(
            new TypedCollection(
                'stdClass',
                []
            )
        );
    }
}
