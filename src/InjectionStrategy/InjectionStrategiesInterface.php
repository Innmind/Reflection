<?php
declare(strict_types = 1);

namespace Innmind\Reflection\InjectionStrategy;

use Innmind\Reflection\InjectionStrategyInterface;
use Innmind\Immutable\SetInterface;

/**
 * Repository of InjectionStrategies
 *
 * @author Hugues Maignol <hugues.maignol@kitpages.fr>
 */
interface InjectionStrategiesInterface
{

    /**
     * All the InjectionStrategies.
     *
     * @return SetInterface<InjectionStrategyInterface>
     */
    public function all(): SetInterface;

    /**
     * Returns the relevant injection strategy for the given object, key and value.
     *
     * @param mixed  $object
     * @param string $key
     * @param mixed  $value
     *
     * @return InjectionStrategyInterface
     */
    public function get($object, string $key, $value): InjectionStrategyInterface;
}
