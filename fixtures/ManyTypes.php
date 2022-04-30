<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\Reflection;

final class ManyTypes
{
    private int $a;
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
}
