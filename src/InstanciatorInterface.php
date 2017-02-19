<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Immutable\{
    MapInterface,
    SetInterface
};

interface InstanciatorInterface
{
    /**
     * Build a new instance for the given class
     *
     * @param string $class
     * @param MapInterface<string, mixed> $properties
     *
     * @throws InstanciationFailedException
     *
     * @return object
     */
    public function build(string $class, MapInterface $properties);

    /**
     * Return a collection of parameters it can inject for the given class
     *
     * @param string $class
     *
     * @return SetInterface<string>
     */
    public function parameters(string $class): SetInterface;
}
