<?php
declare(strict_types = 1);

namespace Innmind\Reflection;

use Innmind\Reflection\Exception\LogicException;

interface ExtractionStrategy
{
    /**
     * Check if the injection strategy can be used to extract the given property
     *
     * @param non-empty-string $property
     */
    public function supports(object $object, string $property): bool;

    /**
     * Extract the given property value out of the given object
     *
     * @param non-empty-string $property
     *
     * @throws LogicException If the property is not supported
     */
    public function extract(object $object, string $property): mixed;
}
