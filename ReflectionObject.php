<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Immutable\Collection;
use Innmind\Immutable\CollectionInterface;
use Innmind\Reflection\Exception\InvalidArgumentException;
use Innmind\Reflection\ExtractionStrategy\ExtractionStrategies;
use Innmind\Reflection\ExtractionStrategy\ExtractionStrategiesInterface;
use Innmind\Reflection\InjectionStrategy\InjectionStrategies;
use Innmind\Reflection\InjectionStrategy\InjectionStrategiesInterface;

class ReflectionObject
{
    private $object;
    private $properties;
    private $injectionStrategies;
    private $extractionStrategies;

    public function __construct(
        $object,
        CollectionInterface $properties = null,
        InjectionStrategiesInterface $injectionStrategies = null,
        ExtractionStrategiesInterface $extractionStrategies = null
    ) {
        if (!is_object($object)) {
            throw new InvalidArgumentException;
        }

        $this->injectionStrategies = $injectionStrategies ?? new InjectionStrategies();
        $this->extractionStrategies = $extractionStrategies ?? new ExtractionStrategies();

        $this->object = $object;

        $this->properties = $properties ?? new Collection([]);
    }

    /**
     * Add a property that will be injected
     *
     * @param string $name
     * @param mixed  $value
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
     * @return InjectionStrategiesInterface
     */
    public function getInjectionStrategies(): InjectionStrategiesInterface
    {
        return $this->injectionStrategies;
    }

    /**
     * Return the list of extraction strategies used
     *
     * @return ExtractionStrategiesInterface
     */
    public function getExtractionStrategies(): ExtractionStrategiesInterface
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
     * @param mixed  $value
     *
     * @return void
     */
    private function inject(string $key, $value)
    {
        $this
            ->injectionStrategies
            ->get($this->object, $key, $value)
            ->inject($this->object, $key, $value);
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
        return $this
            ->extractionStrategies
            ->get($this->object, $property)
            ->extract($this->object, $property);
    }
}
