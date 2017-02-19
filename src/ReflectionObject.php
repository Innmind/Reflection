<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\Exception\InvalidArgumentException;
use Innmind\Reflection\ExtractionStrategy\ExtractionStrategies;
use Innmind\Reflection\ExtractionStrategy\ExtractionStrategiesInterface;
use Innmind\Reflection\InjectionStrategy\InjectionStrategies;
use Innmind\Reflection\InjectionStrategy\InjectionStrategiesInterface;
use Innmind\Immutable\{
    MapInterface,
    Map
};

class ReflectionObject
{
    private $object;
    private $properties;
    private $injectionStrategies;
    private $extractionStrategies;

    public function __construct(
        $object,
        MapInterface $properties = null,
        InjectionStrategiesInterface $injectionStrategies = null,
        ExtractionStrategiesInterface $extractionStrategies = null
    ) {
        $properties = $properties ?? new Map('string', 'mixed');

        if (
            !is_object($object) ||
            (string) $properties->keyType() !== 'string' ||
            (string) $properties->valueType() !== 'mixed'
        ) {
            throw new InvalidArgumentException;
        }

        $this->object = $object;
        $this->properties = $properties;
        $this->injectionStrategies = $injectionStrategies ?? new InjectionStrategies();
        $this->extractionStrategies = $extractionStrategies ?? new ExtractionStrategies();
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
            $this->properties->put($name, $value),
            $this->injectionStrategies,
            $this->extractionStrategies
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
            $this->object,
            $map,
            $this->injectionStrategies,
            $this->extractionStrategies
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
    public function injectionStrategies(): InjectionStrategiesInterface
    {
        return $this->injectionStrategies;
    }

    /**
     * Return the list of extraction strategies used
     *
     * @return ExtractionStrategiesInterface
     */
    public function extractionStrategies(): ExtractionStrategiesInterface
    {
        return $this->extractionStrategies;
    }

    /**
     * Return the object with the list of properties set on it
     *
     * @return object
     */
    public function build()
    {
        $this->properties->foreach(function(string $key, $value): void {
            $this->inject($key, $value);
        });

        return $this->object;
    }

    /**
     * Extract the given list of properties
     *
     * @param string[] $properties
     *
     * @return MapInterface<string, mixed>
     */
    public function extract(array $properties): MapInterface
    {
        $map = new Map('string', 'mixed');

        foreach ($properties as $property) {
            $map = $map->put(
                $property,
                $this->extractProperty($property)
            );
        }

        return $map;
    }

    /**
     * Inject the given key/value pair into the object
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    private function inject(string $key, $value): void
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
