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

final class ReflectionInstanciator implements Instanciator
{
    /**
     * {@inheritdoc}
     */
    public function build(string $class, Map $properties): object
    {
        try {
            $refl = new \ReflectionClass($class);

            if (!$refl->hasMethod('__construct')) {
                return $refl->newInstance();
            }

            $constructor = $refl->getMethod('__construct');

            return $refl->newInstanceArgs(
                $this
                    ->computeArguments($constructor, $properties)
                    ->reduce(
                        [],
                        function(array $carry, string $property, $value): array {
                            $carry[$property] = $value;

                            return $carry;
                        },
                    ),
            );
        } catch (\TypeError $e) {
            throw new InstanciationFailed($class, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parameters(string $class): Set
    {
        $parameters = Set::strings();
        $refl = new \ReflectionClass($class);

        if (!$refl->hasMethod('__construct')) {
            return $parameters;
        }

        $refl = $refl->getMethod('__construct');

        foreach ($refl->getParameters() as $parameter) {
            $parameters = ($parameters)($parameter->name);
        }

        return $parameters;
    }

    /**
     * @param ReflectionMethod $constructor
     * @param Map<string, variable> $properties
     *
     * @return Map<string, variable>
     */
    private function computeArguments(
        \ReflectionMethod $constructor,
        Map $properties
    ): Map {
        $arguments = $properties->clear();

        foreach ($constructor->getParameters() as $parameter) {
            if ($this->canInject($parameter, $properties)) {
                $arguments = ($arguments)(
                    $parameter->name,
                    $properties->get($parameter->name),
                );
            }
        }

        return $arguments;
    }

    /**
     * @param ReflectionParameter $parameter
     * @param Map<string, variable> $properties
     *
     * @return bool
     */
    private function canInject(
        \ReflectionParameter $parameter,
        Map $properties
    ): bool {
        if (
            !$parameter->allowsNull() &&
            !$properties->contains($parameter->name)
        ) {
            return false;
        } else if (
            $parameter->allowsNull() &&
            !$properties->contains($parameter->name)
        ) {
            return false;
        }

        $property = $properties->get($parameter->name);

        if ($parameter->hasType()) {
            $type = $parameter->getType();

            if ($type->isBuiltin()) {
                return $type->getName() === \gettype($property);
            } else if (!\is_object($property)) {
                return false;
            }

            $refl = new \ReflectionObject($property);
            $wishedClass = $type->getName();

            return \get_class($property) === $wishedClass ||
                $refl->isSubClassOf($wishedClass);
        }

        return true;
    }
}
