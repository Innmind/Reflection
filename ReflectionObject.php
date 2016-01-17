<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\Exception\InvalidArgumentException;
use Innmind\Reflection\Exception\LogicException;
use Innmind\Immutable\Collection;
use Innmind\Immutable\CollectionInterface;
use Innmind\Immutable\TypedCollectionInterface;

class ReflectionObject
{
    private $object;
    private $properties;
    private $injectionStrategies;
    private $extractionStrategies;

    public function __construct(
        $object,
        CollectionInterface $properties = null,
        TypedCollectionInterface $injectionStrategies = null,
        TypedCollectionInterface $extractionStrategies = null
    ) {
        if (!is_object($object)) {
            throw new InvalidArgumentException;
        }

        $injectionStrategies = $injectionStrategies ?? InjectionStrategies::defaults();
        $extractionStrategies = $extractionStrategies ?? ExtractionStrategies::defaults();

        if ($injectionStrategies->getType() !== InjectionStrategyInterface::class) {
            throw new InvalidArgumentException;
        }

        if ($extractionStrategies->getType() !== ExtractionStrategyInterface::class) {
            throw new InvalidArgumentException;
        }

        $this->object = $object;

        $this->properties = $properties ?? new Collection([]);
        $this->injectionStrategies = $injectionStrategies;
        $this->extractionStrategies = $extractionStrategies;
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
            $this->injectionStrategies,
            $this->extractionStrategies
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
            $this->injectionStrategies,
            $this->extractionStrategies
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
     * Return the list of extraction strategies used
     *
     * @return TypedCollectionInterface
     */
    public function getExtractionStrategies(): TypedCollectionInterface
    {
        return $this->extractionStrategies;
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
     * Extract the given list of properties
     *
     * @param array $properties
     *
     * @return CollectionInterface
     */
    public function extract(array $properties): CollectionInterface
    {
        $values = [];

        foreach ($properties as $property) {
            $values[$property] = $this->extractProperty($property);
        }

        return new Collection($values);
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
     * Extract the given property out of the object
     *
     * @param string $property
     *
     * @return mixed
     */
    private function extractProperty(string $property)
    {
        foreach ($this->extractionStrategies as $strategy) {
            if ($strategy->supports($this->object, $property)) {
                return $strategy->extract($this->object, $property);
            }
        }

        throw new LogicException(sprintf(
            'Property "%s" cannot be extracted',
            $property
        ));
    }
}
