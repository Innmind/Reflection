<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\{
    InjectionStrategy\InjectionStrategies,
    Instanciator\ReflectionInstanciator,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable\{
    Map,
    Set,
};

/**
 * @template T of object
 */
final class ReflectionClass
{
    /** @var class-string<T> */
    private string $class;
    /** @var InjectionStrategy<T> */
    private InjectionStrategy $injectionStrategy;
    /** @var Instanciator<T> */
    private Instanciator $instanciator;

    /**
     * @param class-string<T> $class
     */
    private function __construct(
        string $class,
        InjectionStrategy $injectionStrategy = null,
        Instanciator $instanciator = null,
    ) {
        $this->class = $class;
        /** @var InjectionStrategy<T> */
        $this->injectionStrategy = $injectionStrategy ?? InjectionStrategies::default();
        /** @var Instanciator<T> */
        $this->instanciator = $instanciator ?? new ReflectionInstanciator;
    }

    /**
     * @template V of object
     *
     * @param class-string<V> $class
     *
     * @return self<V>
     */
    public static function of(
        string $class,
        InjectionStrategy $injectionStrategy = null,
        Instanciator $instanciator = null,
    ): self {
        return new self($class, $injectionStrategy, $instanciator);
    }

    /**
     * Return a new instance of the class
     *
     * @param Map<non-empty-string, mixed>|null $properties
     *
     * @return T
     */
    public function build(Map $properties = null): object
    {
        $properties ??= Map::of();

        $object = $this->instanciator->build($this->class, $properties);
        $parameters = $this->instanciator->parameters($this->class);

        //avoid injecting the properties already used in the constructor
        $properties = $properties->filter(
            static fn(string $property) => !$parameters->contains($property),
        );

        return ReflectionObject::of($object, $this->injectionStrategy)->build(
            $properties,
        );
    }

    /**
     * Return all the properties defined on the class
     *
     * It will not extract properties defined in a parent class
     *
     * @return Set<ReflectionProperty<T>>
     */
    public function properties(): Set
    {
        $refl = new \ReflectionClass($this->class);
        /** @var Set<ReflectionProperty<T>> */
        $properties = Set::strings();

        foreach ($refl->getProperties() as $property) {
            /** @psalm-suppress ArgumentTypeCoercion */
            $properties = ($properties)(ReflectionProperty::of(
                $this->class,
                $property->getName(),
            ));
        }

        return $properties;
    }
}
