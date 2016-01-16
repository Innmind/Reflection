<?php
declare(strict_types = 1);

namespace Innmind\Reflection\Instanciator;

use Innmind\Reflection\InstanciatorInterface;
use Innmind\Reflection\Exception\InstanciationFailedException;
use Innmind\Immutable\CollectionInterface;
use Innmind\Immutable\Collection;

class ReflectionInstanciator implements InstanciatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function build(string $class, CollectionInterface $properties)
    {
        try {
            $refl = new \ReflectionClass($class);
            $constructor = $refl->getMethod('__construct');

            return $refl->newInstanceArgs(
                $this
                    ->computeArguments($constructor, $properties)
                    ->toPrimitive()
            );
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
        $refl = new \ReflectionClass($class);
        $refl = $refl->getMethod('__construct');
        $parameters = [];

        foreach ($refl->getParameters() as $parameter) {
            $parameters[] = $parameter->name;
        }

        return new Collection($parameters);
    }

    /**
     * @param ReflectionMethod $constructor
     * @param CollectionInterface $properties
     *
     * @return CollectionInterface
     */
    private function computeArguments(
        \ReflectionMethod $constructor,
        CollectionInterface $properties
    ): CollectionInterface {
        $arguments = new Collection([]);

        foreach ($constructor->getParameters() as $parameter) {
            if ($this->canInject($parameter, $properties)) {
                $arguments = $arguments->set(
                    $parameter->name,
                    $properties[$parameter->name]
                );
            }
        }

        return $arguments;
    }

    /**
     * @param ReflectionParameter $parameter
     * @param CollectionInterface $properties
     *
     * @return bool
     */
    private function canInject(
        \ReflectionParameter $parameter,
        CollectionInterface $properties
    ): bool {
        if (
            !$parameter->allowsNull() &&
            !$properties->hasKey($parameter->name)
        ) {
            return false;
        } else if (
            $parameter->allowsNull() &&
            !$properties->hasKey($parameter->name)
        ) {
            return false;
        }

        $property = $properties[$parameter->name];

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
