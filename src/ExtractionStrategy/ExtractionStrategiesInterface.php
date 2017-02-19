<?php
declare(strict_types = 1);

namespace Innmind\Reflection\ExtractionStrategy;

use Innmind\Reflection\ExtractionStrategyInterface;
use Innmind\Immutable\SetInterface;

/**
 * Repository of extractionStrategies
 *
 * @author Hugues Maignol <hugues.maignol@kitpages.fr>
 */
interface ExtractionStrategiesInterface
{
    /**
     * All the ExtractionStrategies
     *
     * @return SetInterface<ExtractionStrategyInterface>
     */
    public function all(): SetInterface;

    /**
     * Get the relevant ExctactionStrategyInterface for the given object and key.
     *
     * @param  mixed $object
     * @param string $key
     *
     * @return ExtractionStrategyInterface
     *
     */
    public function get($object, string $key): ExtractionStrategyInterface;
}
