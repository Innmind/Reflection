<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Immutable\TypedCollection;

/**
 * Repository of extractionStrategies
 *
 * @author Hugues Maignol <hugues.maignol@kitpages.fr>
 */
interface ExtractionStrategies
{
    /**
     * All the ExtractionStrategies
     *
     * @return TypedCollection
     */
    public function all() : TypedCollection;

    /**
     * Get the relevant ExctactionStrategyInterface for the given object and key.
     *
     * @param  mixed $object
     * @param string $key
     *
     * @return ExtractionStrategyInterface
     *
     */
    public function get($object, string $key) : ExtractionStrategyInterface;
}
