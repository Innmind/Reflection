<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\{
    ExtractionStrategy\ExtractionStrategies,
    InjectionStrategy\InjectionStrategies,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable\Map;
use function Innmind\Immutable\assertMap;

final class ReflectionObject
{
    private $object;
    private $properties;
    private $injectionStrategy;
    private $extractionStrategy;

    public function __construct(
        object $object,
        Map $properties = null,
        InjectionStrategy $injectionStrategy = null,
        ExtractionStrategy $extractionStrategy = null
    ) {
        $properties ??= Map::of('string', 'mixed');

        assertMap('string', 'mixed', $properties, 2);

        $this->object = $object;
        $this->properties = $properties;
        $this->injectionStrategy = $injectionStrategy ?? InjectionStrategies::default();
        $this->extractionStrategy = $extractionStrategy ?? ExtractionStrategies::default();
    }

    public static function of(
        object $object,
        Map $properties = null,
        InjectionStrategy $injectionStrategy = null,
        ExtractionStrategy $extractionStrategy = null
    ): self {
        return new self($object, $properties, $injectionStrategy, $extractionStrategy);
    }

    /**
     * Add a property that will be injected
     *
     * @param mixed  $value
     */
    public function withProperty(string $name, $value): self
    {
        return new self(
            $this->object,
            ($this->properties)($name, $value),
            $this->injectionStrategy,
            $this->extractionStrategy
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
            $map = ($map)($key, $value);
        }

        return new self(
            $this->object,
            $map,
            $this->injectionStrategy,
            $this->extractionStrategy
        );
    }

    /**
     * Return the object with the list of properties set on it
     */
    public function build(): object
    {
        return $this->properties->reduce(
            $this->object,
            function(object $object, string $key, $value): object {
                return $this->inject($object, $key, $value);
            }
        );
    }

    /**
     * Extract the given list of properties
     *
     * @return Map<string, mixed>
     */
    public function extract(string ...$properties): Map
    {
        $map = Map::of('string', 'mixed');

        foreach ($properties as $property) {
            $map = ($map)(
                $property,
                $this->extractProperty($property)
            );
        }

        return $map;
    }

    /**
     * Inject the given key/value pair into the object
     *
     * @param mixed  $value
     */
    private function inject(object $object, string $key, $value): object
    {
        return $this->injectionStrategy->inject($object, $key, $value);
    }

    /**
     * Extract the given property out of the object
     *
     * @return mixed
     */
    private function extractProperty(string $property)
    {
        return $this->extractionStrategy->extract($this->object, $property);
    }
}
