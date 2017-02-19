<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\Reflection;

final class WithConstructor
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
