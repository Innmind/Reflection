<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\Exception\PropertyCannotBeInjected;

/**
 * @template T of object
 */
interface InjectionStrategy
{
    /**
     * Check if the injection strategy can be used to inject the given
     * property and value into the given object
     *
     * @param mixed $value
     */
    public function supports(object $object, string $property, $value): bool;

    /**
     * Inject the given value into the given object
     *
     * @param T $object
     * @param mixed $value
     *
     * @throws PropertyCannotBeInjected If the property is not supported
     *
     * @return T
     */
    public function inject(object $object, string $property, $value): object;
}
