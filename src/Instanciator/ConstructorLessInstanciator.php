<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Instanciator;

use Innmind\Reflection\{
    InstanciatorInterface,
    Exception\InstanciationFailedException
};
use Innmind\Immutable\{
    CollectionInterface,
    Collection
};

final class ConstructorLessInstanciator implements InstanciatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(string $class, CollectionInterface $properties)
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
    public function getParameters(string $class): CollectionInterface
    {
        return new Collection([]);
    }
}
