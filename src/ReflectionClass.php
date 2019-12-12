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
use function Innmind\Immutable\assertMap;

final class ReflectionClass
{
    private $class;
    private $properties;
    private $injectionStrategy;
    private $instanciator;

    public function __construct(
        string $class,
        Map $properties = null,
        InjectionStrategy $injectionStrategy = null,
        Instanciator $instanciator = null
    ) {
        $properties ??= Map::of('string', 'mixed');

        assertMap('string', 'mixed', $properties, 2);

        $this->class = $class;
        $this->properties = $properties;
        $this->injectionStrategy = $injectionStrategy ?? InjectionStrategies::default();
        $this->instanciator = $instanciator ?? new ReflectionInstanciator;
    }

    public static function of(
        string $class,
        Map $properties = null,
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
            ($this->properties)($property, $value),
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
            $map = ($map)($key, $value);
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
