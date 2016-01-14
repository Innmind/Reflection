<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\Exception\InvalidArgumentException;
use Innmind\Reflection\Exception\LogicException;
use Innmind\Reflection\InjectionStrategy\SetterStrategy;
use Innmind\Reflection\InjectionStrategy\NamedMethodStrategy;
use Innmind\Reflection\InjectionStrategy\ReflectionStrategy;
use Innmind\Immutable\Collection;
use Innmind\Immutable\CollectionInterface;
use Innmind\Immutable\TypedCollection;
use Innmind\Immutable\TypedCollectionInterface;

class ReflectionObject
{
    private $object;
    private $properties;
    private $injectionStrategies;

    public function __construct(
        $object,
        CollectionInterface $properties = null,
        TypedCollectionInterface $injectionStrategies = null
    ) {
        if (!is_object($object)) {
            throw new InvalidArgumentException;
        }

        $this->object = $object;

        $this->initProperties($properties);
        $this->initInjectionStrategies($injectionStrategies);
    }

    /**
     * Add a property that will be injected
     *
     * @param string $name
     * @param mixed $value
     *
     * @return self
     */
    public function withProperty(string $name, $value)
    {
        return new self(
            $this->object,
            $this->properties->set($name, $value),
            $this->injectionStrategies
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
            $this->object,
            $this->properties->merge(new Collection($properties)),
            $this->injectionStrategies
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
     * Return the object with the list of properties set on it
     *
     * @return object
     */
    public function buildObject()
    {
        foreach ($this->properties as $key => $value) {
            $this->inject($key, $value);
        }

        return $this->object;
    }

    /**
     * Inject the given key/value pair into the object
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    private function inject(string $key, $value)
    {
        foreach ($this->injectionStrategies as $strategy) {
            if ($strategy->supports($this->object, $key, $value)) {
                $strategy->inject($this->object, $key, $value);

                return;
            }
        }

        throw new LogicException(sprintf(
            'Property "%s" cannot be injected',
            $key
        ));
    }

    /**
     * @param CollectionInterface|null $properties
     *
     * @return void
     */
    private function initProperties(CollectionInterface $properties = null)
    {
        if ($properties === null) {
            $properties = new Collection([]);
        }

        $this->properties = $properties;
    }

    /**
     * @param TypedCollectionInterface $strategies
     *
     * @return void
     */
    private function initInjectionStrategies(TypedCollectionInterface $strategies = null)
    {
        if ($strategies === null) {
            $strategies = new TypedCollection(
                InjectionStrategyInterface::class,
                [
                    new SetterStrategy,
                    new NamedMethodStrategy,
                    new ReflectionStrategy,
                ]
            );
        }

        if ($strategies->getType() !== InjectionStrategyInterface::class) {
            throw new InvalidArgumentException;
        }

        $this->injectionStrategies = $strategies;
    }
}
