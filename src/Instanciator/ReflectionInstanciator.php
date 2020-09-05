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
    Sequence,
};
use function Innmind\Immutable\unwrap;

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
                unwrap($this->computeArguments($constructor, $properties)),
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
     * @param Map<string, mixed> $properties
     *
     * @return Sequence<mixed>
     */
    private function computeArguments(
        \ReflectionMethod $constructor,
        Map $properties
    ): Sequence {
        $arguments = Sequence::mixed();

        foreach ($constructor->getParameters() as $parameter) {
            if ($this->canInject($parameter, $properties)) {
                $arguments = ($arguments)($properties->get($parameter->name));
            }
        }

        return $arguments;
    }

    /**
     * @param Map<string, mixed> $properties
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

        /** @var mixed */
        $property = $properties->get($parameter->name);

        if ($parameter->hasType()) {
            /** @var \ReflectionNamedType $type */
            $type = $parameter->getType();

            if ($type->isBuiltin()) {
                return $type->getName() === \gettype($property);
            }

            if (!\is_object($property)) {
                return false;
            }

            $refl = new \ReflectionObject($property);
            /** @var class-string */
            $wishedClass = $type->getName();

            return \get_class($property) === $wishedClass ||
                $refl->isSubClassOf($wishedClass);
        }

        return true;
    }
}
