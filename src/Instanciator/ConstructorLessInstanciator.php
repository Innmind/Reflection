<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Instanciator;

use Innmind\Reflection\{
    Instanciator,
    Exception\InstanciationFailed,
};
use Innmind\Immutable\{
    MapInterface,
    SetInterface,
    Map,
    Set,
};

final class ConstructorLessInstanciator implements Instanciator
{
    /**
     * {@inheritdoc}
     */
    public function build(string $class, MapInterface $properties): object
    {
        try {
            $refl = new \ReflectionClass($class);

            return $refl->newInstanceWithoutConstructor();
        } catch (\TypeError $e) {
            throw new InstanciationFailed($class, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parameters(string $class): SetInterface
    {
        return new Set('string');
    }
}
