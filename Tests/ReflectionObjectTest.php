<?php
declare(strict_types=1);

namespace Innmind\Reflection\Tests;

use Innmind\Reflection\ReflectionObject;

class ReflectionObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildWithoutProperties()
    {
        $o = new \stdClass;
        $refl = new ReflectionObject($o);

        $o2 = $refl->buildObject();

        $this->assertSame($o, $o2);
        $this->assertSame([], $refl->getProperties()->toPrimitive());
    }

    /**
     * @expectedException Innmind\Reflection\Exception\InvalidArgumentException
     */
    public function testThrowWhenNotUsingAnObject()
    {
        new ReflectionObject([]);
    }

    public function testAddPropertyToInject()
    {
        $o = new \stdClass;
        $refl = new ReflectionObject($o);
        $refl2 = $refl->withProperty('foo', 'bar');

        $this->assertInstanceOf(ReflectionObject::class, $refl2);
        $this->assertNotSame($refl, $refl2);
        $this->assertSame([], $refl->getProperties()->toPrimitive());
        $this->assertSame(
            ['foo' => 'bar'],
            $refl2->getProperties()->toPrimitive()
        );
    }

    public function testBuild()
    {
        $o = new class() {
            private $a;
            protected $b;
            private $c;
            private $d;

            public function setC($value)
            {
                $this->c = $value;
            }

            public function d($value)
            {
                $this->d = $value;
            }

            public function dump()
            {
                return [$this->a, $this->b, $this->c, $this->d];
            }
        };

        $this->assertSame([null, null, null, null], $o->dump());

        (new ReflectionObject($o))
            ->withProperty('a', 1)
            ->withProperty('b', 2)
            ->withProperty('c', 3)
            ->withProperty('d', 4)
            ->buildObject();

        $this->assertSame([1, 2, 3, 4], $o->dump());
    }

    public function testBuildWithProperties()
    {
        $o = new class() {
            private $a;
            protected $b;
            private $c;
            private $d;

            public function setC($value)
            {
                $this->c = $value;
            }

            public function d($value)
            {
                $this->d = $value;
            }

            public function dump()
            {
                return [$this->a, $this->b, $this->c, $this->d];
            }
        };

        $this->assertSame([null, null, null, null], $o->dump());

        (new ReflectionObject($o))
            ->withProperties([
                'a' => 1,
                'b' => 2
            ])
            ->withProperties([
                'c' => 3,
                'd' => 4,
            ])
            ->buildObject();

        $this->assertSame([1, 2, 3, 4], $o->dump());
    }

    /**
     * @expectedException Innmind\Reflection\Exception\LogicException
     * @expectedExceptionMessage Property "a" cannot be injected
     */
    public function testThrowWhenPropertyNotFound()
    {
        (new ReflectionObject(new \stdClass))
            ->withProperty('a', 1)
            ->buildObject();
    }

    /**
     * @expectedException Innmind\Reflection\Exception\LogicException
     * @expectedExceptionMessage Property "a" cannot be injected
     */
    public function testThrowWhenNameMethodDoesntHaveParameter()
    {
        $o = new class() {
            public function a()
            {
                //pass
            }
        };
        (new ReflectionObject($o))
            ->withProperty('a', 1)
            ->buildObject();
    }
}
