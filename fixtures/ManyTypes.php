<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\Reflection;

final class ManyTypes
{
    #[Attr('foo')]
    private int $a;
    #[Attr('bar')]
    private float $b;
    private string $c;
    private bool $d;
    private array $e;
    private object $f;
    private \Closure $g;
    private NoConstructor $h;
    private mixed $i;
    private $j;
    private ?NoConstructor $k;
    private int|string $union;
    private \Countable&\ArrayAccess $intersection;

    public function a(): int
    {
        return $this->a;
    }

    public function b(): float
    {
        return $this->b;
    }

    public function d(): bool
    {
        return $this->d;
    }
}
