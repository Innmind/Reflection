<?php
declare(strict_types = 1);

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\Exception\LogicException;

interface InjectionStrategyInterface
{
    /**
     * Check if the injection strategy can be used to inject the given
     * property and value into the given object
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     *
     * @return bool
     */
    public function supports($object, string $property, $value): bool;

    /**
     * Inject the given value into the given object
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     *
     * @throws LogicException If the property is not supported
     *
     * @return void
     */
    public function inject($object, string $property, $value);
}
