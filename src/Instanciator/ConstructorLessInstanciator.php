<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Instanciator;

use Innmind\Reflection\{
    Instanciator,
    Exception\InstanciationFailed,
};
use Innmind\Immutable\{
    Map,
    Set,
};

final class ConstructorLessInstanciator implements Instanciator
{
    public function build(string $class, Map $properties): object
    {
        try {
            $refl = new \ReflectionClass($class);

            return $refl->newInstanceWithoutConstructor();
        } catch (\TypeError $e) {
            throw new InstanciationFailed($class, $e);
        }
    }

    public function parameters(string $class): Set
    {
        return Set::strings();
    }
}
