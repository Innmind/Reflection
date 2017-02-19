<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\{
    InjectionStrategy\InjectionStrategies,
    Instanciator\ReflectionInstanciator,
    Exception\InvalidArgumentException
};
use Innmind\Immutable\{
    MapInterface,
    Map
};

class ReflectionClass
{
    private $class;
    private $properties;
    private $injectionStrategy;
    private $instanciator;

    public function __construct(
        string $class,
        MapInterface $properties = null,
        InjectionStrategyInterface $injectionStrategy = null,
        InstanciatorInterface $instanciator = null
    ) {
        $properties = $properties ?? new Map('string', 'mixed');

        if (
            (string) $properties->keyType() !== 'string' ||
            (string) $properties->valueType() !== 'mixed'
        ) {
            throw new InvalidArgumentException;
        }

        $this->class = $class;
        $this->properties = $properties;
        $this->injectionStrategy = $injectionStrategy ?? InjectionStrategies::default();
        $this->instanciator = $instanciator ?? new ReflectionInstanciator;
    }

    /**
     * Add a property to be injected in the new object
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return self
     */
    public function withProperty(string $property, $value): self
    {
        return new self(
            $this->class,
            $this->properties->put($property, $value),
            $this->injectionStrategy,
            $this->instanciator
        );
    }

    /**
     * Add a set of properties that need to be injected
     *
     * @param array<string, mixed> $properties
     *
     * @return self
     */
    public function withProperties(array $properties): self
    {
        $map = $this->properties;

        foreach ($properties as $key => $value) {
            $map = $map->put($key, $value);
        }

        return new self(
            $this->class,
            $map,
            $this->injectionStrategy,
            $this->instanciator
        );
    }

    /**
     * Return the collection of properties that will be injected in the object
     *
     * @return MapInterface<string, mixed>
     */
    public function properties(): MapInterface
    {
        return $this->properties;
    }

    /**
     * Return the list of injection strategies used
     *
     * @return InjectionStrategiesInterface
     */
    public function injectionStrategy(): InjectionStrategyInterface
    {
        return $this->injectionStrategy;
    }

    /**
     * Return the object instanciator
     *
     * @return InstanciatorInterface
     */
    public function instanciator(): InstanciatorInterface
    {
        return $this->instanciator;
    }

    /**
     * Return a new instance of the class
     *
     * @return object
     */
    public function build()
    {
        $object = $this->instanciator->build($this->class, $this->properties);
        $parameters = $this->instanciator->parameters($this->class);

        //avoid injecting the properties already used in the constructor
        $properties = $this
            ->properties
            ->filter(function(string $property) use ($parameters) {
                return !$parameters->contains($property);
            });
        $refl = new ReflectionObject(
            $object,
            $properties,
            $this->injectionStrategy
        );

        return $refl->build();
    }
}
