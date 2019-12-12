<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Immutable\{
    Map,
    Set,
};

interface Instanciator
{
    /**
     * Build a new instance for the given class
     *
     * @param Map<string, mixed> $properties
     *
     * @throws InstanciationFailedException
     */
    public function build(string $class, Map $properties): object;

    /**
     * Return a collection of parameters it can inject for the given class
     *
     * @return Set<string>
     */
    public function parameters(string $class): Set;
}
