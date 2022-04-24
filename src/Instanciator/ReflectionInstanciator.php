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

final class ReflectionInstanciator implements Instanciator
{
    public function build(string $class, Map $properties): object
    {
        try {
            $refl = new \ReflectionClass($class);

            if (!$refl->hasMethod('__construct')) {
                return $refl->newInstance();
            }

            $constructor = $refl->getMethod('__construct');

            return $refl->newInstanceArgs(
                $this->computeArguments($constructor, $properties)->toList(),
            );
        } catch (\TypeError $e) {
            throw new InstanciationFailed($class, $e);
        }
    }

    public function parameters(string $class): Set
    {
        /** @var Set<non-empty-string> */
        $parameters = Set::strings();
        $refl = new \ReflectionClass($class);

        if (!$refl->hasMethod('__construct')) {
            return $parameters;
        }

        $refl = $refl->getMethod('__construct');

        foreach ($refl->getParameters() as $parameter) {
            /** @psalm-suppress ArgumentTypeCoercion */
            $parameters = ($parameters)($parameter->name);
        }

        return $parameters;
    }

    /**
     * @param Map<non-empty-string, mixed> $properties
     *
     * @return Sequence<mixed>
     */
    private function computeArguments(
        \ReflectionMethod $constructor,
        Map $properties,
    ): Sequence {
        $arguments = Sequence::mixed();

        foreach ($constructor->getParameters() as $parameter) {
            /** @psalm-suppress ArgumentTypeCoercion */
            $arguments = $properties
                ->get($parameter->name)
                ->filter(fn() => $this->canInject($parameter, $properties))
                ->match(
                    static fn($property) => ($arguments)($property),
                    static fn() => $arguments,
                );
        }

        return $arguments;
    }

    /**
     * @param Map<non-empty-string, mixed> $properties
     */
    private function canInject(
        \ReflectionParameter $parameter,
        Map $properties,
    ): bool {
        /** @psalm-suppress ArgumentTypeCoercion */
        if (
            !$parameter->allowsNull() &&
            !$properties->contains($parameter->name)
        ) {
            return false;
        }

        /** @psalm-suppress ArgumentTypeCoercion */
        if (
            $parameter->allowsNull() &&
            !$properties->contains($parameter->name)
        ) {
            return false;
        }

        /** @psalm-suppress ArgumentTypeCoercion */
        return $properties
            ->get($parameter->name)
            ->match(
                fn($value) => $this->canInjectValue($parameter, $value),
                static fn() => true,
            );
    }

    private function canInjectValue(
        \ReflectionParameter $parameter,
        mixed $property,
    ): bool {
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
            $wishedClass = $type->getName();

            return \get_class($property) === $wishedClass ||
                $refl->isSubClassOf($wishedClass);
        }

        return true;
    }
}
