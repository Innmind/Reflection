<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\Reflection;

final class NoConstructor
{
    private $a;

    public function a()
    {
        return $this->a;
    }
}
