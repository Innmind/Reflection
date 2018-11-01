<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Instanciator;

use Innmind\Reflection\{
    InstanciatorInterface,
    Exception\InstanciationFailedException
};
use Innmind\Immutable\{
    MapInterface,
    SetInterface,
    Map,
    Set
};

final class ConstructorLessInstanciator implements InstanciatorInterface
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
            throw new InstanciationFailedException(
                sprintf(
                    'Class "%s" cannot be instanciated',
                    $class
                ),
                $e->getCode(),
                $e
            );
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
