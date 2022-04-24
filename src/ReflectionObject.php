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
    private function __construct(
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
     * @param Map<non-empty-string, mixed>|null $properties
     *
     * @return T
     */
    public function build(Map $properties = null): object
    {
        $properties ??= Map::of();

        /** @psalm-suppress InvalidArgument */
        return $properties->reduce(
            $this->object,
            fn(object $object, string $key, mixed $value): object => $this->inject($object, $key, $value),
        );
    }

    /**
     * Extract the given list of properties
     *
     * @param non-empty-string $properties
     *
     * @return Map<non-empty-string, mixed>
     */
    public function extract(string ...$properties): Map
    {
        /** @var Map<non-empty-string, mixed> */
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
     * @param non-empty-string $key
     *
     * @return T
     */
    private function inject(object $object, string $key, mixed $value): object
    {
        return $this->injectionStrategy->inject($object, $key, $value);
    }

    /**
     * Extract the given property out of the object
     *
     * @param non-empty-string $property
     */
    private function extractProperty(string $property): mixed
    {
        return $this->extractionStrategy->extract($this->object, $property);
    }
}
