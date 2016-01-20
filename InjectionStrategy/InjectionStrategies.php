<?php
declare(strict_types = 1);

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Immutable\TypedCollection;

/**
 * Repository of InjectionStrategies
 *
 * @author Hugues Maignol <hugues.maignol@kitpages.fr>
 */
interface InjectionStrategies
{

    /**
     * All the InjectionStrategies.
     *
     * @return TypedCollection
     */
    public function all() : TypedCollection;

    /**
     * Returns the relevant injection strategy for the given object, key and value.
     *
     * @param mixed  $object
     * @param string $key
     * @param mixed  $value
     *
     * @return InjectionStrategyInterface
     */
    public function get($object, string $key, $value) : InjectionStrategyInterface;
}