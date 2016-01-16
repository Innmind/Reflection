<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\InjectionStrategy\SetterStrategy;
use Innmind\Reflection\InjectionStrategy\NamedMethodStrategy;
use Innmind\Reflection\InjectionStrategy\ReflectionStrategy;
use Innmind\Reflection\Instanciator\ReflectionInstanciator;
use Innmind\Immutable\Collection;
use Innmind\Immutable\CollectionInterface;
use Innmind\Immutable\TypedCollection;
use Innmind\Immutable\TypedCollectionInterface;

class ReflectionClass
{
    private $class;
    private $properties;
    private $injectionStrategies;
    private $instanciator;

    public function __construct(
        string $class,
        CollectionInterface $properties = null,
        TypedCollectionInterface $injectionStrategies = null,
        InstanciatorInterface $instanciator = null
    ) {
        $this->class = $class;
        $this->properties = $properties ?? new Collection([]);
        $this->initInjectionStrategies($injectionStrategies);
        $this->instanciator = $instanciator ?? new ReflectionInstanciator;
    }

    /**
     * Add a property to be injected in the new object
     *
     * @param string $property
     * @param mixed $value
     *
     * @return self
     */
    public function withProperty(string $property, $value): self
    {
        return new self(
            $this->class,
            $this->properties->set($property, $value),
            $this->injectionStrategies,
            $this->instanciator
        );
    }

    /**
     * Add a set of properties that need to be injected
     *
     * @param array $properties
     *
     * @return self
     */
    public function withProperties(array $properties): self
    {
        return new self(
            $this->class,
            $this->properties->merge(new Collection($properties)),
            $this->injectionStrategies,
            $this->instanciator
        );
    }

    /**
     * Return the collection of properties that will be injected in the object
     *
     * @return CollectionInterface
     */
    public function getProperties(): CollectionInterface
    {
        return $this->properties;
    }

    /**
     * Return the list of injection strategies used
     *
     * @return TypedCollectionInterface
     */
    public function getInjectionStrategies(): TypedCollectionInterface
    {
        return $this->injectionStrategies;
    }

    /**
     * Return the object instanciator
     *
     * @return InstanciatorInterface
     */
    public function getInstanciator(): InstanciatorInterface
    {
        return $this->instanciator;
    }

    /**
     * Return a new instance of the class
     *
     * @return object
     */
    public function buildObject()
    {
        $object = $this->instanciator->build($this->class, $this->properties);
        $parameters = $this->instanciator->getParameters($this->class);

        //avoid injecting the properties already used in the constructor
        $properties = $this
            ->properties
            ->filter(function ($value, $property) use ($parameters) {
                return !$parameters->contains($property);
            });
        $refl = new ReflectionObject(
            $object,
            $properties,
            $this->injectionStrategies
        );

        return $refl->buildObject();
    }

    /**
     * @param TypedCollectionInterface $strategies
     *
     * @return void
     */
    private function initInjectionStrategies(TypedCollectionInterface $strategies = null)
    {
        $strategies = $strategies ?? new TypedCollection(
            InjectionStrategyInterface::class,
            [
                new SetterStrategy,
                new NamedMethodStrategy,
                new ReflectionStrategy,
            ]
        );

        if ($strategies->getType() !== InjectionStrategyInterface::class) {
            throw new InvalidArgumentException;
        }

        $this->injectionStrategies = $strategies;
    }
}
