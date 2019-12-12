<?php
declare(strict_types = 1);

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\{
    InjectionStrategy,
    Exception\LogicException,
};
use Innmind\Immutable\Str;

/**
 * Looks for a method named exactly like the property
 *
 * Example:
 * <code>
 * private $foo;
 *
 * public function foo($foo);
 * </code>
 */
final class NamedMethodStrategy implements InjectionStrategy
{
    /**
     * {@inheritdoc}
     */
    public function supports(object $object, string $property, $value): bool
    {
        $refl = new \ReflectionObject($object);

        $property = Str::of($property)
            ->camelize()
            ->toString();

        if (!$refl->hasMethod($property)) {
            return false;
        }

        $property = $refl->getMethod($property);

        if (!$property->isPublic()) {
            return false;
        }

        return $property->getNumberOfParameters() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function inject(object $object, string $property, $value): object
    {
        if (!$this->supports($object, $property, $value)) {
            throw new LogicException;
        }

        $property = Str::of($property)
            ->camelize()
            ->toString();

        $object->$property($value);

        return $object;
    }
}
