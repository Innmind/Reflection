<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\{
    InjectionStrategy\DelegationStrategy,
    InjectionStrategyInterface
};
use Innmind\Immutable\Stream;
use PHPUnit\Framework\TestCase;

class DelegationStrategyTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            InjectionStrategyInterface::class,
            new DelegationStrategy(
                new Stream(InjectionStrategyInterface::class)
            )
        );
    }

    public function testSupports()
    {
        $strategy = new DelegationStrategy(
            (new Stream(InjectionStrategyInterface::class))
                ->add(
                    $mock1 = $this->createMock(InjectionStrategyInterface::class)
                )
                ->add(
                    $mock2 = $this->createMock(InjectionStrategyInterface::class)
                )
        );
        $object = new \stdClass;
        $property = 'foo';
        $value = 'bar';
        $mock1
            ->expects($this->at(0))
            ->method('supports')
            ->with($object, $property, $value)
            ->willReturn(false);
        $mock1
            ->expects($this->at(1))
            ->method('supports')
            ->with($object, $property, $value)
            ->willReturn(true);
        $mock1
            ->expects($this->at(2))
            ->method('supports')
            ->with($object, $property, $value)
            ->willReturn(false);
        $mock2
            ->expects($this->at(0))
            ->method('supports')
            ->with($object, $property, $value)
            ->willReturn(true);
        $mock2
            ->expects($this->at(1))
            ->method('supports')
            ->with($object, $property, $value)
            ->willReturn(false);

        $this->assertTrue($strategy->supports($object, $property, $value));
        $this->assertTrue($strategy->supports($object, $property, $value));
        $this->assertFalse($strategy->supports($object, $property, $value));
    }

    public function testInjectWithFirstStrategy()
    {
        $strategy = new DelegationStrategy(
            (new Stream(InjectionStrategyInterface::class))
                ->add(
                    $mock1 = $this->createMock(InjectionStrategyInterface::class)
                )
                ->add(
                    $mock2 = $this->createMock(InjectionStrategyInterface::class)
                )
        );
        $object = new \stdClass;
        $property = 'foo';
        $value = 'bar';
        $mock1
            ->expects($this->once())
            ->method('supports')
            ->with($object, $property, $value)
            ->willReturn(true);
        $mock2
            ->expects($this->never())
            ->method('supports');
        $mock1
            ->expects($this->once())
            ->method('inject')
            ->with($object, $property, $value);
        $mock2
            ->expects($this->never())
            ->method('inject');

        $this->assertNull($strategy->inject($object, $property, $value));
    }

    public function testCacheStrategy()
    {
        $strategy = new DelegationStrategy(
            (new Stream(InjectionStrategyInterface::class))
                ->add(
                    $mock1 = $this->createMock(InjectionStrategyInterface::class)
                )
                ->add(
                    $mock2 = $this->createMock(InjectionStrategyInterface::class)
                )
        );
        $object = new \stdClass;
        $property = 'foo';
        $value = 'bar';
        $mock1
            ->expects($this->once())
            ->method('supports')
            ->with($object, $property, $value)
            ->willReturn(true);
        $mock2
            ->expects($this->never())
            ->method('supports');
        $mock1
            ->expects($this->exactly(2))
            ->method('inject')
            ->with($object, $property, $value);
        $mock2
            ->expects($this->never())
            ->method('inject');

        $this->assertNull($strategy->inject($object, $property, $value));
        $this->assertNull($strategy->inject($object, $property, $value));
    }

    public function testExtractWithSecondStrategy()
    {
        $strategy = new DelegationStrategy(
            (new Stream(InjectionStrategyInterface::class))
                ->add(
                    $mock1 = $this->createMock(InjectionStrategyInterface::class)
                )
                ->add(
                    $mock2 = $this->createMock(InjectionStrategyInterface::class)
                )
        );
        $object = new \stdClass;
        $property = 'foo';
        $value = 'bar';
        $mock1
            ->expects($this->once())
            ->method('supports')
            ->with($object, $property, $value)
            ->willReturn(false);
        $mock2
            ->expects($this->once())
            ->method('supports')
            ->with($object, $property, $value)
            ->willReturn(true);
        $mock1
            ->expects($this->never())
            ->method('inject');
        $mock2
            ->expects($this->once())
            ->method('inject')
            ->with($object, $property, $value);

        $this->assertNull($strategy->inject($object, $property, $value));
    }

    /**
     * @expectedException Innmind\Reflection\Exception\PropertyNotFoundException
     */
    public function testThrowWhenNoStrategySupporting()
    {
        $strategy = new DelegationStrategy(
            (new Stream(InjectionStrategyInterface::class))
                ->add(
                    $mock1 = $this->createMock(InjectionStrategyInterface::class)
                )
                ->add(
                    $mock2 = $this->createMock(InjectionStrategyInterface::class)
                )
        );
        $object = new \stdClass;
        $property = 'foo';
        $value = 'bar';
        $mock1
            ->expects($this->once())
            ->method('supports')
            ->with($object, $property, $value)
            ->willReturn(false);
        $mock2
            ->expects($this->once())
            ->method('supports')
            ->with($object, $property, $value)
            ->willReturn(false);
        $mock1
            ->expects($this->never())
            ->method('inject');
        $mock2
            ->expects($this->never())
            ->method('inject');

        $strategy->inject($object, $property, $value);
    }
}
