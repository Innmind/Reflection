<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Immutable\{
    Map,
    Set,
};

/**
 * @template T of object
 */
final class ReflectionClass
{
    /** @var class-string<T> */
    private string $class;

    /**
     * @param class-string<T> $class
     */
    private function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @template V of object
     *
     * @param class-string<V> $class
     *
     * @return self<V>
     */
    public static function of(string $class): self
    {
        return new self($class);
    }

    /**
     * Return all the properties defined on the class
     *
     * It will not extract properties defined in a parent class
     *
     * @return Set<ReflectionProperty<T>>
     */
    public function properties(): Set
    {
        $refl = new \ReflectionClass($this->class);
        /** @var Set<ReflectionProperty<T>> */
        $properties = Set::strings();

        foreach ($refl->getProperties() as $property) {
            /** @psalm-suppress ArgumentTypeCoercion */
            $properties = ($properties)(ReflectionProperty::of(
                $this->class,
                $property->getName(),
            ));
        }

        return $properties;
    }
}
