<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\{
    InjectionStrategy\InjectionStrategies,
    Instanciator\ReflectionInstanciator,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable\{
    MapInterface,
    Map,
    SetInterface,
    Set,
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
        InjectionStrategy $injectionStrategy = null,
        Instanciator $instanciator = null
    ) {
        $properties = $properties ?? new Map('string', 'mixed');

        if (
            (string) $properties->keyType() !== 'string' ||
            (string) $properties->valueType() !== 'mixed'
        ) {
            throw new \TypeError('Argument 2 must be of type MapInterface<string, mixed>');
        }

        $this->class = $class;
        $this->properties = $properties;
        $this->injectionStrategy = $injectionStrategy ?? InjectionStrategies::default();
        $this->instanciator = $instanciator ?? new ReflectionInstanciator;
    }

    public static function of(
        string $class,
        MapInterface $properties = null,
        InjectionStrategy $injectionStrategy = null,
        Instanciator $instanciator = null
    ): self {
        return new self($class, $properties, $injectionStrategy, $instanciator);
    }

    /**
     * Add a property to be injected in the new object
     *
     * @param mixed  $value
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
     * Return a new instance of the class
     */
    public function build(): object
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

    /**
     * Return all the properties defined on the class
     *
     * It will not extract properties defined in a parent class
     *
     * @return SetInterface<string>
     */
    public function properties(): SetInterface
    {
        $refl = new \ReflectionClass($this->class);
        $properties = Set::of('string');

        foreach ($refl->getProperties() as $property) {
            $properties = $properties->add($property->getName());
        }

        return $properties;
    }
}
