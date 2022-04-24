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
    /** @var Map<string, mixed> */
    private Map $properties;
    /** @var InjectionStrategy<T> */
    private InjectionStrategy $injectionStrategy;
    /** @var Instanciator<T> */
    private Instanciator $instanciator;

    /**
     * @param class-string<T> $class
     * @param Map<string, mixed>|null $properties
     */
    public function __construct(
        string $class,
        Map $properties = null,
        InjectionStrategy $injectionStrategy = null,
        Instanciator $instanciator = null,
    ) {
        $this->class = $class;
        $this->properties = $properties ?? Map::of();
        /** @var InjectionStrategy<T> */
        $this->injectionStrategy = $injectionStrategy ?? InjectionStrategies::default();
        /** @var Instanciator<T> */
        $this->instanciator = $instanciator ?? new ReflectionInstanciator;
    }

    /**
     * @template V of object
     *
     * @param class-string<V> $class
     * @param Map<string, mixed>|null $properties
     *
     * @return self<V>
     */
    public static function of(
        string $class,
        Map $properties = null,
        InjectionStrategy $injectionStrategy = null,
        Instanciator $instanciator = null,
    ): self {
        return new self($class, $properties, $injectionStrategy, $instanciator);
    }

    /**
     * Add a property to be injected in the new object
     *
     * @return self<T>
     */
    public function withProperty(string $property, mixed $value): self
    {
        return new self(
            $this->class,
            ($this->properties)($property, $value),
            $this->injectionStrategy,
            $this->instanciator,
        );
    }

    /**
     * Add a set of properties that need to be injected
     *
     * @param array<string, mixed> $properties
     *
     * @return self<T>
     */
    public function withProperties(array $properties): self
    {
        $map = $this->properties;

        /** @var mixed $value */
        foreach ($properties as $key => $value) {
            $map = ($map)($key, $value);
        }

        return new self(
            $this->class,
            $map,
            $this->injectionStrategy,
            $this->instanciator,
        );
    }

    /**
     * Return a new instance of the class
     *
     * @return T
     */
    public function build(): object
    {
        $object = $this->instanciator->build($this->class, $this->properties);
        $parameters = $this->instanciator->parameters($this->class);

        //avoid injecting the properties already used in the constructor
        $properties = $this
            ->properties
            ->filter(static fn(string $property) => !$parameters->contains($property));
        $refl = new ReflectionObject(
            $object,
            $properties,
            $this->injectionStrategy,
        );

        return $refl->build();
    }

    /**
     * Return all the properties defined on the class
     *
     * It will not extract properties defined in a parent class
     *
     * @return Set<string>
     */
    public function properties(): Set
    {
        $refl = new \ReflectionClass($this->class);
        $properties = Set::strings();

        foreach ($refl->getProperties() as $property) {
            $properties = ($properties)($property->getName());
        }

        return $properties;
    }
}
