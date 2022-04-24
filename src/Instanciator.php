<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\Exception\InstanciationFailed;
use Innmind\Immutable\{
    Map,
    Set,
};

/**
 * @template T of object
 */
interface Instanciator
{
    /**
     * Build a new instance for the given class
     *
     * @param class-string<T> $class
     * @param Map<non-empty-string, mixed> $properties
     *
     * @throws InstanciationFailed
     *
     * @return T
     */
    public function build(string $class, Map $properties): object;

    /**
     * Return a collection of parameters it can inject for the given class
     *
     * @param class-string $class
     *
     * @return Set<non-empty-string>
     */
    public function parameters(string $class): Set;
}
