<?php
declare(strict_types = 1);

namespace Tests\Innmind\Reflection;

use Innmind\Reflection\InjectionStrategy\InjectionStrategiesInterface;
use Innmind\Reflection\InjectionStrategyInterface;
use Innmind\Reflection\InjectionStrategy\NamedMethodStrategy;
use Innmind\Reflection\InjectionStrategy\ReflectionStrategy;
use Innmind\Reflection\InjectionStrategy\SetterStrategy;
use Innmind\Reflection\Instanciator\ReflectionInstanciator;
use Innmind\Reflection\InstanciatorInterface;
use Innmind\Reflection\ReflectionClass;
use Innmind\Immutable\{
    MapInterface,
    SetInterface,
    Set
};
use PHPUnit\Framework\TestCase;

class ReflectionClassTest extends TestCase
{
    public function testBuildWithoutProperties()
    {
        $r = new ReflectionClass(NoConstruct::class);

        $o = $r->build();

        $this->assertInstanceOf(NoConstruct::class, $o);
        $this->assertSame(null, $o->a());
    }

    public function testBuild()
    {
        $o = (new ReflectionClass(NoConstruct::class))
            ->withProperty('a', 42)
            ->build();

        $this->assertInstanceOf(NoConstruct::class, $o);
        $this->assertSame(42, $o->a());

        $o = (new ReflectionClass(WithConstruct::class))
            ->withProperties(
                [
                    'a' => 24,
                    'b' => 66,
                ]
            )
            ->build();

        $this->assertInstanceOf(WithConstruct::class, $o);
        $this->assertSame(24, $o->a());
        $this->assertSame(66, $o->b());
    }

    public function testGetInjectionStrategies()
    {
        $refl = new ReflectionClass('stdClass');

        $s = $refl->injectionStrategies()->all();
        $this->assertSame(InjectionStrategyInterface::class, (string) $s->type());
        $s = $s->toPrimitive();
        $this->assertInstanceOf(SetterStrategy::class, $s[0]);
        $this->assertInstanceOf(NamedMethodStrategy::class, $s[1]);
        $this->assertInstanceOf(ReflectionStrategy::class, $s[2]);

        $testInjectionStrategies = $this->getMockBuilder(InjectionStrategiesInterface::class)
            ->getMock();
        $testInjectionStrategies->expects($this->any())
            ->method('all')
            ->will(
                $this->returnValue(
                    (new Set(InjectionStrategyInterface::class))
                        ->add($s = new ReflectionStrategy)
                )
            );

        $refl = new ReflectionClass(
            'stdClass',
            null,
            $testInjectionStrategies
        );
        $this->assertSame($s, $refl->injectionStrategies()->all()->current());
    }

    public function testGetProperties()
    {
        $r = (new ReflectionClass('foo'))
            ->withProperty('a', 'b');

        $props = $r->properties();

        $this->assertInstanceOf(MapInterface::class, $props);
        $this->assertSame('string', (string) $props->keyType());
        $this->assertSame('mixed', (string) $props->valueType());
        $this->assertCount(1, $props);
        $this->assertSame('b', $props->get('a'));
    }

    public function testGetInstanciator()
    {
        $r = new ReflectionClass('foo');
        $i = $r->instanciator();
        $this->assertInstanceOf(ReflectionInstanciator::class, $i);

        $i = new class implements InstanciatorInterface
        {
            public function build(string $class, MapInterface $properties)
            {
            }

            public function parameters(string $class): SetInterface
            {
            }
        };

        $r = new ReflectionClass('foo', null, null, $i);
        $i2 = $r->instanciator();
        $this->assertSame($i, $i2);
    }
}

class NoConstruct
{
    private $a;

    public function a()
    {
        return $this->a;
    }
}

class WithConstruct
{
    private $a;
    private $b;

    public function __construct($a)
    {
        $this->a = $a;
    }

    public function setA($a)
    {
        $this->a = 42;
    }

    public function a()
    {
        return $this->a;
    }

    public function b()
    {
        return $this->b;
    }
}
