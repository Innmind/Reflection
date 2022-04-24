<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\{
    ExtractionStrategy\ExtractionStrategies,
    InjectionStrategy\InjectionStrategies,
    Exception\InvalidArgumentException,
};
use Innmind\Immutable\Map;

/**
 * @template T of object
 */
final class ReflectionObject
{
    /** @var T */
    private object $object;
    /** @var InjectionStrategy<T> */
    private InjectionStrategy $injectionStrategy;
    private ExtractionStrategy $extractionStrategy;

    /**
     * @param T $object
     */
    public function __construct(
        object $object,
        InjectionStrategy $injectionStrategy = null,
        ExtractionStrategy $extractionStrategy = null,
    ) {
        $this->object = $object;
        /** @var InjectionStrategy<T> */
        $this->injectionStrategy = $injectionStrategy ?? InjectionStrategies::default();
        $this->extractionStrategy = $extractionStrategy ?? ExtractionStrategies::default();
    }

    /**
     * @template V of object
     *
     * @param V $object
     *
     * @return self<V>
     */
    public static function of(
        object $object,
        InjectionStrategy $injectionStrategy = null,
        ExtractionStrategy $extractionStrategy = null,
    ): self {
        return new self($object, $injectionStrategy, $extractionStrategy);
    }

    /**
     * Return the object with the list of properties set on it
     *
     * @param Map<string, mixed>|null $properties
     *
     * @return T
     */
    public function build(Map $properties = null): object
    {
        $properties ??= Map::of();

        /** @psalm-suppress InvalidArgument */
        return $properties->reduce(
            $this->object,
            fn(object $object, string $key, $value): object => $this->inject($object, $key, $value),
        );
    }

    /**
     * Extract the given list of properties
     *
     * @return Map<string, mixed>
     */
    public function extract(string ...$properties): Map
    {
        /** @var Map<string, mixed> */
        $map = Map::of();

        foreach ($properties as $property) {
            $map = ($map)(
                $property,
                $this->extractProperty($property),
            );
        }

        return $map;
    }

    /**
     * Inject the given key/value pair into the object
     *
     * @param T $object
     * @param mixed  $value
     *
     * @return T
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
