<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Immutable\CollectionInterface;

interface InstanciatorInterface
{
    /**
     * Build a new instance for the given class
     *
     * @param string $class
     * @param CollectionInterface $properties
     *
     * @throws InstanciationFailedException
     *
     * @return object
     */
    public function build(string $class, CollectionInterface $properties);

    /**
     * Return a collection of parameters it can inject for the given class
     *
     * @param string $class
     *
     * @return CollectionInterface
     */
    public function getParameters(string $class): CollectionInterface;
}
