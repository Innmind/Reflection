<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Immutable\{
    MapInterface,
    SetInterface,
};

interface Instanciator
{
    /**
     * Build a new instance for the given class
     *
     * @param MapInterface<string, mixed> $properties
     *
     * @throws InstanciationFailedException
     */
    public function build(string $class, MapInterface $properties): object;

    /**
     * Return a collection of parameters it can inject for the given class
     *
     * @return SetInterface<string>
     */
    public function parameters(string $class): SetInterface;
}
