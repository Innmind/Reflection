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

class ReflectionInstanciator implements Instanciator
{
    /**
     * {@inheritdoc}
     */
    public function build(string $class, MapInterface $properties): object
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
                        }
                    )
            );
        } catch (\TypeError $e) {
            throw new InstanciationFailed(
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
        $parameters = new Set('string');
        $refl = new \ReflectionClass($class);

        if (!$refl->hasMethod('__construct')) {
            return $parameters;
        }

        $refl = $refl->getMethod('__construct');

        foreach ($refl->getParameters() as $parameter) {
            $parameters = $parameters->add($parameter->name);
        }

        return $parameters;
    }

    /**
     * @param ReflectionMethod $constructor
     * @param MapInterface<string, variable> $properties
     *
     * @return MapInterface<string, variable>
     */
    private function computeArguments(
        \ReflectionMethod $constructor,
        MapInterface $properties
    ): MapInterface {
        $arguments = $properties->clear();

        foreach ($constructor->getParameters() as $parameter) {
            if ($this->canInject($parameter, $properties)) {
                $arguments = $arguments->put(
                    $parameter->name,
                    $properties->get($parameter->name)
                );
            }
        }

        return $arguments;
    }

    /**
     * @param ReflectionParameter $parameter
     * @param MapInterface<string, variable> $properties
     *
     * @return bool
     */
    private function canInject(
        \ReflectionParameter $parameter,
        MapInterface $properties
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
                return (string) $type === gettype($property);
            } else if (!is_object($property)) {
                return false;
            }

            $refl = new \ReflectionObject($property);
            $wishedClass = (string) $type;

            return get_class($property) === $wishedClass ||
                $refl->isSubClassOf($wishedClass);
        }

        return true;
    }
}
