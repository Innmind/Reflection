<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\Exception\LogicException;

interface ExtractionStrategyInterface
{
    /**
     * Check if the injection strategy can be used to extract the given property
     *
     * @param object $object
     * @param string $property
     *
     * @return bool
     */
    public function supports(object $object, string $property): bool;

    /**
     * Extract the given property value out of the given object
     *
     * @param object $object
     * @param string $property
     *
     * @throws LogicException If the property is not supported
     *
     * @return mixed
     */
    public function extract(object $object, string $property);
}
