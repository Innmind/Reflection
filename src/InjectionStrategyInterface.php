<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\Exception\LogicException;

interface InjectionStrategyInterface
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
     * @param mixed $value
     *
     * @throws PropertyCannotBeInjectedException If the property is not supported
     */
    public function inject(object $object, string $property, $value): void;
}
